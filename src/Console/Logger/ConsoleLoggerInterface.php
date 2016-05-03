<?php

namespace Accompli\Console\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ConsoleLoggerInterface.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
interface ConsoleLoggerInterface extends LoggerInterface
{
    /**
     * Returns the verbosity of the OutputInterface instance.
     *
     * @return int
     */
    public function getVerbosity();

    /**
     * Returns the instance handling the output to the console.
     *
     * @param string $level
     *
     * @return OutputInterface
     */
    public function getOutput($level = LogLevel::NOTICE);
}
