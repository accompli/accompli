<?php

namespace Accompli\Console\Helper;

use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Title.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class Title
{
    /**
     * The OutputInterface instance.
     *
     * @var OutputInterface
     */
    protected $output;

    /**
     * The title message to be rendered.
     *
     * @var string
     */
    protected $message;

    /**
     * Constructs a new Title instance.
     *
     * @param OutputInterface $output
     * @param string          $message
     */
    public function __construct(OutputInterface $output, $message)
    {
        $this->output = $output;
        $this->message = $message;
    }

    /**
     * Renders the title to output.
     */
    public function render()
    {
        $this->output->writeln(array(
            '',
            sprintf('<title>%s</>', $this->message),
            sprintf('<title>%s</>', str_repeat('=', Helper::strlenWithoutDecoration($this->output->getFormatter(), $this->message))),
            '',
        ));
    }
}
