<?php

namespace Accompli\Console\Helper;

use Accompli\Console\Application;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * TitleBlock.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class TitleBlock extends Title
{
    /**
     * @var string
     */
    const STYLE_SUCCESS = 'success';

    /**
     * @var string
     */
    const STYLE_ERRORED_SUCCESS = 'errored-success';

    /**
     * @var string
     */
    const STYLE_FAILURE = 'failure';

    /**
     * The styles for title blocks.
     *
     * @var array
     */
    private $blockStyles = array(
        self::STYLE_SUCCESS => array(
            'prefix' => '[OK]',
            'style' => 'fg=black;bg=green',
        ),
        self::STYLE_ERRORED_SUCCESS => array(
            'prefix' => '[OK, but with errors]',
            'style' => 'fg=black;bg=yellow',
        ),
        self::STYLE_FAILURE => array(
            'prefix' => '[FAILURE]',
            'style' => 'fg=white;bg=red',
        ),
    );

    /**
     * The specified render style for this title block.
     *
     * @var string
     */
    private $style;

    /**
     * Constructs a new TitleBlock instance.
     *
     * @param OutputInterface $output
     * @param string          $message
     * @param string          $style
     */
    public function __construct(OutputInterface $output, $message, $style)
    {
        parent::__construct($output, $message);

        $this->style = $style;
    }

    /**
     * Renders the title block to output.
     */
    public function render()
    {
        $this->output->writeln('');

        $lineLength = $this->getTerminalWidth();

        $lines = explode(PHP_EOL, wordwrap($this->message, $lineLength - (strlen($this->blockStyles[$this->style]['prefix']) + 3), PHP_EOL, true));
        array_unshift($lines, ' ');
        array_push($lines, ' ');

        foreach ($lines as $i => $line) {
            $prefix = str_repeat(' ', strlen($this->blockStyles[$this->style]['prefix']));
            if ($i === 1) {
                $prefix = $this->blockStyles[$this->style]['prefix'];
            }

            $line = sprintf(' %s %s', $prefix, $line);
            $this->output->writeln(sprintf('<%s>%s%s</>', $this->blockStyles[$this->style]['style'], $line, str_repeat(' ', $lineLength - Helper::strlenWithoutDecoration($this->output->getFormatter(), $line))));
        }

        $this->output->writeln('');
    }

    /**
     * Returns the terminal width.
     *
     * @return int
     */
    private function getTerminalWidth()
    {
        $application = new Application();

        return $application->getTerminalWidth();
    }
}
