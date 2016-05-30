<?php

namespace Accompli\Test\Console\Command;

use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * Eases the testing of console commands.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class CommandTester
{
    /**
     * The Command instance.
     *
     * @var Command
     */
    private $command;

    /**
     * The InputInterface instance.
     *
     * @var InputInterface
     */
    private $input;

    /**
     * The OutputInterface instance.
     *
     * @var OutputInterface
     */
    private $output;

    /**
     * The exit code of the command.
     *
     * @var int
     */
    private $statusCode;

    /**
     * The input stream resource.
     *
     * @var resource
     */
    private $inputStream;

    /**
     * Constructs a new CommandTester instance.
     *
     * @param Command $command A Command instance to test.
     */
    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    /**
     * Sets the string to an input stream.
     *
     * @param string $input
     */
    public function setInputStream($input)
    {
        $this->inputStream = fopen('php://memory', 'r+', false);
        fwrite($this->inputStream, $input);
        rewind($this->inputStream);

        $questionHelper = $this->command->getHelperSet()->get('question');
        if ($questionHelper instanceof QuestionHelper) {
            $questionHelper->setInputStream($this->inputStream);
        }
    }

    /**
     * Injects a value into a property of the command.
     *
     * @param string $property
     * @param mixed  $value
     */
    public function injectIntoCommandProperty($property, $value)
    {
        $reflectionClass = new ReflectionClass(get_class($this->command));
        if ($reflectionClass->hasProperty($property)) {
            $reflectionProperty = $reflectionClass->getProperty($property);
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($this->command, $value);
        }
    }

    /**
     * Executes the command.
     *
     * Available execution options:
     *
     *  * interactive: Sets the input interactive flag
     *  * decorated:   Sets the output decorated flag
     *  * verbosity:   Sets the output verbosity flag
     *
     * @param InputInterface|array $input   An InputInterface instance or array of command arguments and options
     * @param array                $options An array of execution options
     * @param StreamOutput|null    $output  An StreamOutput instance
     *
     * @return int The command exit code
     */
    public function execute($input, array $options = array(), StreamOutput $output = null)
    {
        // set the command name automatically if the application requires
        // this argument and no command name was passed
        if (is_array($input)
            && !isset($input['command'])
            && (null !== $application = $this->command->getApplication())
            && $application->getDefinition()->hasArgument('command')
        ) {
            $input = array_merge(array('command' => $this->command->getName()), $input);
        }

        $this->input = $input;
        if ($this->input instanceof InputInterface === false) {
            $this->input = new ArrayInput($this->input);
        }
        if (isset($options['interactive'])) {
            $this->input->setInteractive($options['interactive']);
        }

        $this->output = $output;
        if ($this->output instanceof StreamOutput === false) {
            $this->output = new StreamOutput(fopen('php://memory', 'w', false));
        }
        if (isset($options['decorated'])) {
            $this->output->setDecorated($options['decorated']);
        }
        if (isset($options['verbosity'])) {
            $this->output->setVerbosity($options['verbosity']);
        }

        return $this->statusCode = $this->command->run($this->input, $this->output);
    }

    /**
     * Gets the display returned by the last execution of the command.
     *
     * @param bool $normalize Whether to normalize end of lines to \n or not
     *
     * @return string The display
     */
    public function getDisplay($normalize = false)
    {
        rewind($this->output->getStream());

        $display = stream_get_contents($this->output->getStream());

        if ($normalize) {
            $display = str_replace(PHP_EOL, "\n", $display);
        }

        return $display;
    }

    /**
     * Gets the input instance used by the last execution of the command.
     *
     * @return InputInterface The current input instance
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Returns the input stream resource.
     *
     * @return resource
     */
    public function getInputStream()
    {
        return $this->inputStream;
    }

    /**
     * Gets the output instance used by the last execution of the command.
     *
     * @return OutputInterface The current output instance
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Gets the status code returned by the last execution of the application.
     *
     * @return int The status code
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
