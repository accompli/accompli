<?php

namespace Accompli\DependencyInjection;

use Accompli\Console\Logger\ConsoleLoggerInterface;

/**
 * LoggerAwareInterface.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
interface LoggerAwareInterface
{
    /**
     * Sets the console logger.
     *
     * @param ConsoleLoggerInterface $logger
     */
    public function setLogger(ConsoleLoggerInterface $logger);
}
