<?php

namespace Accompli\Deployment\Connection;

use Accompli\Deployment\Host;
use Nijens\Utilities\ObjectFactory;

/**
 * ConnectionManager.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class ConnectionManager implements ConnectionManagerInterface
{
    /**
     * The array with connection types and accompanying connection adapter classes.
     *
     * @var array
     */
    private $connectionAdapters = array();

    /**
     * The array with connection adapters.
     *
     * @var ConnectionAdapterInterface[]
     */
    private $connections = array();

    /**
     * {@inheritdoc}
     */
    public function registerConnectionAdapter($connectionType, $connectionAdapterClass)
    {
        if (class_exists($connectionAdapterClass) && in_array('Accompli\Deployment\Connection\ConnectionAdapterInterface', class_implements($connectionAdapterClass))) {
            $this->connectionAdapters[$connectionType] = $connectionAdapterClass;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectionAdapter(Host $host)
    {
        if (isset($this->connectionAdapters[$host->getConnectionType()])) {
            $connectionIdentifier = spl_object_hash($host);

            if (isset($this->connections[$connectionIdentifier]) === false) {
                $connectionAdapterArguments = $host->getConnectionOptions();
                $connectionAdapterArguments['hostname'] = $host->getHostname();

                $this->connections[$connectionIdentifier] = ObjectFactory::getInstance()->newInstance($this->connectionAdapters[$host->getConnectionType()], $connectionAdapterArguments);
            }

            return $this->connections[$connectionIdentifier];
        }
    }
}
