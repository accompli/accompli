<?php

namespace Accompli\Deployment\Connection;

use Accompli\Deployment\Host;
use Accompli\EventDispatcher\Event\HostEvent;
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

    /**
     * Sets a connection adapter on a Host when an 'accompli.create_connection' event is dispatched.
     *
     * @param HostEvent $event
     */
    public function onCreateConnection(HostEvent $event)
    {
        $connectionAdapter = $this->getConnectionAdapter($event->getHost());
        if ($connectionAdapter instanceof ConnectionAdapterInterface) {
            $event->getHost()->setConnection($connectionAdapter);
        }
    }
}
