<?php

namespace Accompli\Console\Formatter;

use Psr\Log\LogLevel;
use Symfony\Component\Console\Formatter\OutputFormatter as BaseOutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Formatter\OutputFormatterStyleInterface;

/**
 * OutputFormatter.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class OutputFormatter extends BaseOutputFormatter
{
    /**
     * Constructs a new OutputFormatter.
     *
     * @param bool                            $decorated
     * @param OutputFormatterStyleInterface[] $styles
     */
    public function __construct($decorated = false, array $styles = array())
    {
        parent::__construct($decorated, $styles);

        $this->setStyle('title', new OutputFormatterStyle());
        $this->setStyle(LogLevel::EMERGENCY, new OutputFormatterStyle('white', 'red'));
        $this->setStyle(LogLevel::CRITICAL, new OutputFormatterStyle('white', 'red'));
        $this->setStyle(LogLevel::ALERT, new OutputFormatterStyle('white', 'red'));
        $this->setStyle(LogLevel::ERROR, new OutputFormatterStyle('white', 'red'));
        $this->setStyle(LogLevel::WARNING, new OutputFormatterStyle('white', 'red'));
        $this->setStyle(LogLevel::NOTICE, new OutputFormatterStyle());
        $this->setStyle(LogLevel::INFO, new OutputFormatterStyle('black', 'yellow'));
        $this->setStyle(LogLevel::DEBUG, new OutputFormatterStyle());
        $this->setStyle('event-name', new OutputFormatterStyle('yellow'));
        $this->setStyle('event-task-name', new OutputFormatterStyle('yellow'));
        $this->setStyle('event-task-action-in_progress', new OutputFormatterStyle());
        $this->setStyle('event-task-action-failed', new OutputFormatterStyle('red'));
        $this->setStyle('event-task-action-completed', new OutputFormatterStyle('green'));
    }
}
