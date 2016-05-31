<?php

namespace Accompli\Test\Task;

use Accompli\Deployment\Connection\ConnectionAdapterInterface;
use Accompli\Deployment\Host;
use Accompli\Exception\ConnectionException;
use Accompli\Task\AbstractConnectedTask;
use PHPUnit_Framework_TestCase;

/**
 * AbstractConnectedTaskTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class AbstractConnectedTaskTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if AbstractConnectedTask::ensureConnection returns a connection adapter instance when connected.
     */
    public function testEnsureConnectionReturnsConnectionAdapterWhenConnected()
    {
        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();
        $connectionAdapterMock->expects($this->once())
                ->method('isConnected')
                ->willReturn(true);

        $hostMock = $this->getMockBuilder(Host::class)
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())
                ->method('hasConnection')
                ->willReturn(true);
        $hostMock->expects($this->once())
                ->method('getConnection')
                ->willReturn($connectionAdapterMock);

        $task = $this->getMockBuilder(AbstractConnectedTask::class)
                ->getMockForAbstractClass();

        $this->assertInstanceOf(ConnectionAdapterInterface::class, $task->ensureConnection($hostMock));
    }

    /**
     * Tests if AbstractConnectedTask::ensureConnection returns a connection adapter instance when not connected but able to connect.
     */
    public function testEnsureConnectionReturnsConnectionAdapterWhenAbleToConnect()
    {
        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();
        $connectionAdapterMock->expects($this->once())
                ->method('isConnected')
                ->willReturn(false);
        $connectionAdapterMock->expects($this->once())
                ->method('connect')
                ->willReturn(true);

        $hostMock = $this->getMockBuilder(Host::class)
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())
                ->method('hasConnection')
                ->willReturn(true);
        $hostMock->expects($this->once())
                ->method('getConnection')
                ->willReturn($connectionAdapterMock);

        $task = $this->getMockBuilder(AbstractConnectedTask::class)
                ->getMockForAbstractClass();

        $this->assertInstanceOf(ConnectionAdapterInterface::class, $task->ensureConnection($hostMock));
    }

    /**
     * Tests if AbstractConnectedTask::ensureConnection throws a ConnectionException when the connection adapter is not connected and not able to connect.
     */
    public function testEnsureConnectionThrowsConnectionExceptionWhenNotConnectedAndNotAbleToConnect()
    {
        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();
        $connectionAdapterMock->expects($this->once())
                ->method('isConnected')
                ->willReturn(false);
        $connectionAdapterMock->expects($this->once())
                ->method('connect')
                ->willReturn(false);

        $hostMock = $this->getMockBuilder(Host::class)
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())
                ->method('hasConnection')
                ->willReturn(true);
        $hostMock->expects($this->once())
                ->method('getConnection')
                ->willReturn($connectionAdapterMock);
        $hostMock->expects($this->once())
                ->method('getHostname')
                ->willReturn('accompli.deployment.net');
        $hostMock->expects($this->once())
                ->method('getConnectionType')
                ->willReturn('test');

        $task = $this->getMockBuilder(AbstractConnectedTask::class)
                ->getMockForAbstractClass();

        $this->setExpectedException(ConnectionException::class, 'Could not connect to "accompli.deployment.net" through "test".');

        $task->ensureConnection($hostMock);
    }

    /**
     * Tests if AbstractConnectedTask::ensureConnection throws a ConnectionException when a connection adapter is not available on the Host instance.
     */
    public function testEnsureConnectionThrowsConnectionExceptionWhenNoConnectionAdapterAvailable()
    {
        $hostMock = $this->getMockBuilder(Host::class)
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())
                ->method('hasConnection')
                ->willReturn(false);
        $hostMock->expects($this->once())
                ->method('getConnectionType')
                ->willReturn('test');

        $task = $this->getMockBuilder(AbstractConnectedTask::class)
                ->getMockForAbstractClass();

        $this->setExpectedException(ConnectionException::class, 'No connection adapter of type "test" found on host.');

        $task->ensureConnection($hostMock);
    }
}
