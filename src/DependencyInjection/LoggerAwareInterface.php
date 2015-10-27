<?php

namespace Accompli\DependencyInjection;

use Psr\Log\LoggerInterface;

/**
 * LoggerAwareInterface.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
interface LoggerAwareInterface
{
    /**
     * Sets the logger.
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger);
}
