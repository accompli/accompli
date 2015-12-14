<?php

namespace Accompli\Task;

use Accompli\Deployment\Connection\ConnectionAdapterInterface;
use Accompli\Deployment\Host;
use Accompli\Exception\ConnectionException;

/**
 * AbstractConnectedTask.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
abstract class AbstractConnectedTask implements TaskInterface
{
    /**
     * Ensures a connected connection adapter for a host.
     * Returns the connection adapter instance.
     *
     * @param Host $host
     *
     * @return ConnectionAdapterInterface
     *
     * @throws ConnectionException
     */
    public function ensureConnection(Host $host)
    {
        if ($host->hasConnection()) {
            $connection = $host->getConnection();
            if ($connection->isConnected() === false && $connection->connect() === false) {
                throw new ConnectionException(sprintf('Could not connect to "%s" through "%s".', $host->getHostname(), $host->getConnectionType()));
            }

            return $connection;
        }

        throw new ConnectionException(sprintf('No connection adapter of type "%s" found on host.', $host->getConnectionType()));
    }
}
