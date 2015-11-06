<?php

namespace Accompli\Deployment\Connection;

use Accompli\Deployment\Host;

/**
 * ConnectionManagerInterface.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
interface ConnectionManagerInterface
{
    /**
     * Registers a connection adapter classname for a connection type.
     *
     * @param string $connectionType
     * @param string $connectionAdapterClass
     */
    public function registerConnectionAdapter($connectionType, $connectionAdapterClass);

    /**
     * Returns a connection adapter instance for a Host.
     *
     * @param Host $host
     *
     * @return ConnectionAdapterInterface|null
     */
    public function getConnectionAdapter(Host $host);
}
