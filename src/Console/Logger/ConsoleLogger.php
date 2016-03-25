<?php

namespace Accompli\Console\Logger;

use Accompli\Console\Formatter\OutputFormatter;
use Accompli\Task\TaskInterface;
use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ConsoleLogger.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class ConsoleLogger extends AbstractLogger
{
    /**
     * ANSI code to move the cursor up by the specified number (%d) of lines without changing columns.
     * If the cursor is already on the top line, this sequence is ignored.
     *
     * @var string
     */
    const ANSI_CURSOR_UP_FORMAT = "\033[%dA";

    /**
     * ANSI code to move the cursor forward by the specified number of columns without changing lines.
     * If the cursor is already in the rightmost column, this sequence is ignored.
     *
     * @var string
     */
    const ANSI_CURSOR_FORWARD_FORMAT = "\e[%dC";

    /**
     * ANSI code to move the cursor back by the specified number of columns without changing lines.
     * If the cursor is already in the leftmost column, this sequence is ignored.
     *
     * @var string
     */
    const ANSI_CURSOR_BACKWARD_FORMAT = "\e[%dD";

    /**
     * ANSI code to clear the screen from cursor to end of display. The cursor position is unchanged.
     *
     * @var string
     */
    const ANSI_CLEAR_SCREEN_FROM_CURSOR_TO_END = "\e[0J";

    /**
     * The instance handling the output to the console.
     *
     * @var OutputInterface
     */
    private $output;

    /**
     * The line count of the previous message based on the terminal width.
     *
     * @var int
     */
    private $previousMessageLineCount = 0;

    /**
     * The context array of the previous log message.
     *
     * @var array
     */
    private $previousContext;

    /**
     * The array with mapping of LogLevel constants to OutputInterface verbosity constants.
     *
     * @var array
     */
    private $logLevelToVerbosityLevelMap = array(
        LogLevel::EMERGENCY => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::ALERT => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::CRITICAL => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::ERROR => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::WARNING => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::INFO => OutputInterface::VERBOSITY_VERBOSE,
        LogLevel::DEBUG => OutputInterface::VERBOSITY_VERY_VERBOSE,
    );

    /**
     * The array map for context to message section output.
     *
     * @var array
     */
    private $contextToOutputSectionMap = array(
        'event.name' => array(
            'paddedLength' => 36,
            'format' => '[<event-name>%s</event-name>]',
        ),
        'event.task.name' => array(
            'paddedLength' => 27,
            'format' => '[<event-task-name>%s</event-task-name>]',
        ),
    );

    /**
     * The array map for convering a task action status to output.
     *
     * @var array
     */
    private $taskActionStatusToOutputMap = array(
        TaskInterface::ACTION_IN_PROGRESS => '...',
        TaskInterface::ACTION_COMPLETED => ' ✓ ',
        TaskInterface::ACTION_FAILED => '!!!',
    );

    /**
     * Constructs a new ConsoleLogger instance.
     *
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
        $this->output->setFormatter(new OutputFormatter($this->output->isDecorated()));

        if (substr($this->getOperatingSystemName(), 0, 3) === 'WIN') {
            $this->taskActionStatusToOutputMap['completed'] = ' '.chr(251).' ';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = array())
    {
        if (isset($this->logLevelToVerbosityLevelMap[$level]) === false) {
            throw new InvalidArgumentException(sprintf('The log level "%s" does not exist.', $level));
        }

        $output = $this->getOutput($level);
        if ($output->getVerbosity() >= $this->logLevelToVerbosityLevelMap[$level]) {
            $messageSections = $this->getMessageSectionsFromContext($context);
            if ($output->isDecorated() && isset($context['output.resetLine']) && $context['output.resetLine'] === true) {
                $output->write(sprintf(self::ANSI_CURSOR_UP_FORMAT.self::ANSI_CURSOR_FORWARD_FORMAT.self::ANSI_CLEAR_SCREEN_FROM_CURSOR_TO_END, $this->previousMessageLineCount, FormatterHelper::strlenWithoutDecoration($this->output->getFormatter(), $messageSections)));

                $messageSections = '';
            }

            $message = sprintf('%1$s %2$s <%3$s>%4$s</%3$s>', $messageSections, $this->getTaskActionStatusSectionFromContext($context), $level, $this->interpolate($message, $context));

            $output->writeln($message);

            $this->previousMessageLineCount = $this->getMessageLineCount($message);
            $this->previousContext = $context;
        }
    }

    /**
     * Returns the name of the operating system.
     *
     * @return string
     */
    public function getOperatingSystemName()
    {
        return strtoupper(PHP_OS);
    }

    /**
     * Returns the terminal width.
     *
     * @return int
     */
    public function getTerminalWidth()
    {
        $application = new Application();
        $terminalDimensions = $application->getTerminalDimensions();

        $width = 120;
        if (isset($terminalDimensions[0])) {
            $width = $terminalDimensions[0];
        }

        return $width;
    }

    /**
     * Returns the instance handling the output to the console.
     *
     * @param string $level
     *
     * @return OutputInterface
     */
    private function getOutput($level)
    {
        $output = $this->output;
        if ($this->output instanceof ConsoleOutputInterface && in_array($level, array(LogLevel::EMERGENCY, LogLevel::ALERT, LogLevel::CRITICAL, LogLevel::ERROR))) {
            $output = $this->output->getErrorOutput();
        }

        return $output;
    }

    /**
     * Returns the unique message sections (enclosed by brackets) from the message context.
     *
     * @param array $context
     *
     * @return string
     */
    private function getMessageSectionsFromContext(array $context)
    {
        $messageSections = '';
        foreach ($this->contextToOutputSectionMap as $sectionContextName => $section) {
            if (isset($context[$sectionContextName])) {
                $messageSection = str_repeat(' ', $section['paddedLength']);
                if (isset($this->previousContext[$sectionContextName]) === false || $this->previousContext[$sectionContextName] != $context[$sectionContextName]) {
                    $messageSection = sprintf($section['format'], str_pad($context[$sectionContextName], $section['paddedLength'] - 2));
                }

                $messageSections .= $messageSection;
            }
        }

        return $messageSections;
    }

    /**
     * Returns the task status section based on the context.
     *
     * @param array $context
     *
     * @return string
     */
    private function getTaskActionStatusSectionFromContext(array $context)
    {
        $actionStatusSection = '';
        if ($this->output->isDecorated()) {
            $actionStatusSection = sprintf(self::ANSI_CURSOR_BACKWARD_FORMAT, 1);
        }
        if (isset($context['event.task.action']) && isset($this->taskActionStatusToOutputMap[$context['event.task.action']])) {
            $actionStatusSection = sprintf('[<event-task-action-%1$s>%2$s</event-task-action-%1$s>]', $context['event.task.action'], $this->taskActionStatusToOutputMap[$context['event.task.action']]);
        }

        return $actionStatusSection;
    }

    /**
     * Interpolates context values into the message placeholders.
     *
     * @author PHP Framework Interoperability Group
     *
     * @param string $message
     * @param array  $context
     *
     * @return string
     */
    private function interpolate($message, array $context)
    {
        $replacements = array();
        foreach ($context as $key => $value) {
            if (is_array($value) === false && (is_object($value) === false || method_exists($value, '__toString'))) {
                $replacements[sprintf('{%s}', $key)] = $value;
            }
        }

        return strtr($message, $replacements);
    }

    /**
     * Returns the line count of the message based on the terminal width.
     *
     * @param string $message
     *
     * @return int
     */
    private function getMessageLineCount($message)
    {
        $messageLength = FormatterHelper::strlenWithoutDecoration($this->output->getFormatter(), $message);

        return ceil($messageLength / $this->getTerminalWidth());
    }
}
