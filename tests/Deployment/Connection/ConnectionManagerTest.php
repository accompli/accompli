<?php

namespace Accompli\Test;

use Accompli\Deployment\Connection\ConnectionManager;
use Accompli\Deployment\Host;
use Accompli\Event\HostEvent;
use PHPUnit_Framework_TestCase;

/**
 * ConnectionManagerTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class ConnectionManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if ConnectionManager::registerConnectionType registers connection adapter.
     */
    public function testRegisterConnectionTypeAddsValidConnectionAdapter()
    {
        $connectionAdapterMock = $this->getMockBuilder('Accompli\Deployment\Connection\ConnectionAdapterInterface')->getMock();

        $connectionManager = new ConnectionManager();
        $connectionManager->registerConnectionAdapter('test', get_class($connectionAdapterMock));

        $this->assertAttributeEquals(array('test' => get_class($connectionAdapterMock)), 'connectionAdapters', $connectionManager);
    }

    /**
     * Tests if ConnectionManager::registerConnectionType does not register the invalid connection adapter.
     */
    public function testRegisterConnectionTypeDoesNotAddInvalidConnectionAdapter()
    {
        $connectionManagerMock = $this->getMockBuilder('Accompli\Deployment\Connection\ConnectionManager')->getMock();

        $connectionManager = new ConnectionManager();
        $connectionManager->registerConnectionAdapter('test', get_class($connectionManagerMock));

        $this->assertAttributeEmpty('connectionAdapters', $connectionManager);
    }

    /**
     * Tests if ConnectionManager::registerConnectionType does not register the non-existing connection adapter.
     */
    public function testRegisterConnectionTypeDoesNotAddNonExistingConnectionAdapter()
    {
        $connectionManager = new ConnectionManager();
        $connectionManager->registerConnectionAdapter('test', 'Accompli\Deployment\Connection\DoesNotExist');

        $this->assertAttributeEmpty('connectionAdapters', $connectionManager);
    }

    /**
     * Tests if ConnectionManager::getConnectionAdapter returns null when a connection adapter is not available.
     */
    public function testGetConnectionAdapterReturnsNullWhenConnectionAdapterForConnectionTypeIsNotAvailable()
    {
        $host = new Host('test', 'test', 'example.org', '');

        $connectionManager = new ConnectionManager();

        $this->assertNull($connectionManager->getConnectionAdapter($host));
    }

    /**
     * Tests if ConnectionManager::getConnectionAdapter returns a connection adapter instance.
     */
    public function testGetConnectionAdapterReturnsConnectionAdapter()
    {
        $connectionAdapterMock = $this->getMockBuilder('Accompli\Deployment\Connection\ConnectionAdapterInterface')->getMock();

        $host = new Host('test', 'test', 'example.org', '');

        $connectionManager = new ConnectionManager();
        $connectionManager->registerConnectionAdapter('test', get_class($connectionAdapterMock));

        $this->assertInstanceOf('Accompli\Deployment\Connection\ConnectionAdapterInterface', $connectionManager->getConnectionAdapter($host));
    }

    /**
     * Tests if ConnectionManager::getConnectionAdapter returns the same connection adapter instance.
     *
     * @depends testGetConnectionAdapterReturnsConnectionAdapter
     */
    public function testGetConnectionAdapterAlwaysReturnsTheSameInstance()
    {
        $connectionAdapterMock = $this->getMockBuilder('Accompli\Deployment\Connection\ConnectionAdapterInterface')->getMock();

        $host = new Host('test', 'test', 'example.org', '');

        $connectionManager = new ConnectionManager();
        $connectionManager->registerConnectionAdapter('test', get_class($connectionAdapterMock));

        $returnedConnectionAdapter = $connectionManager->getConnectionAdapter($host);

        $this->assertSame($returnedConnectionAdapter, $connectionManager->getConnectionAdapter($host));
    }

    /**
     * Tests if ConnectionManager::getConnectionAdapter does not return the same connection adapter instance for a different Host instance.
     *
     * @depends testGetConnectionAdapterReturnsConnectionAdapter
     */
    public function testGetConnectionAdapterDoesNotReturnTheSameInstanceForDifferentHost()
    {
        $connectionAdapterMock = $this->getMockBuilder('Accompli\Deployment\Connection\ConnectionAdapterInterface')->getMock();

        $host = new Host('test', 'test', 'example.org', '');
        $hostTwo = new Host('test', 'test', 'example.org', '', array('connectionOption' => 'connectionOptionValue'));

        $connectionManager = new ConnectionManager();
        $connectionManager->registerConnectionAdapter('test', get_class($connectionAdapterMock));

        $returnedConnectionAdapter = $connectionManager->getConnectionAdapter($host);

        $this->assertNotSame($returnedConnectionAdapter, $connectionManager->getConnectionAdapter($hostTwo));
    }

    /**
     * Tests if ConnectionManager::onCreateConnection listener method retrieves a connection adapter and sets it on the host.
     */
    public function testOnCreateConnection()
    {
        $connectionAdapterMock = $this->getMockBuilder('Accompli\Deployment\Connection\ConnectionAdapterInterface')->getMock();

        $connectionManager = new ConnectionManager();
        $connectionManager->registerConnectionAdapter('test', get_class($connectionAdapterMock));

        $host = new Host('test', 'test', 'example.org', '');
        $event = new HostEvent($host);

        $connectionManager->onCreateConnection($event);

        $this->assertInstanceOf('Accompli\Deployment\Connection\ConnectionAdapterInterface', $host->getConnection());
    }
}
