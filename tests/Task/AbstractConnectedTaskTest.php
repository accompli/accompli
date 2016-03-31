<?php

namespace Accompli\Test\Task;

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
        $connectionAdapterMock = $this->getMockBuilder('Accompli\Deployment\Connection\ConnectionAdapterInterface')->getMock();
        $connectionAdapterMock->expects($this->once())->method('isConnected')->willReturn(true);

        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())->method('hasConnection')->willReturn(true);
        $hostMock->expects($this->once())->method('getConnection')->willReturn($connectionAdapterMock);

        $task = $this->getMockBuilder('Accompli\Task\AbstractConnectedTask')->getMockForAbstractClass();

        $this->assertInstanceOf('Accompli\Deployment\Connection\ConnectionAdapterInterface', $task->ensureConnection($hostMock));
    }

    /**
     * Tests if AbstractConnectedTask::ensureConnection returns a connection adapter instance when not connected but able to connect.
     */
    public function testEnsureConnectionReturnsConnectionAdapterWhenAbleToConnect()
    {
        $connectionAdapterMock = $this->getMockBuilder('Accompli\Deployment\Connection\ConnectionAdapterInterface')->getMock();
        $connectionAdapterMock->expects($this->once())->method('isConnected')->willReturn(false);
        $connectionAdapterMock->expects($this->once())->method('connect')->willReturn(true);

        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())->method('hasConnection')->willReturn(true);
        $hostMock->expects($this->once())->method('getConnection')->willReturn($connectionAdapterMock);

        $task = $this->getMockBuilder('Accompli\Task\AbstractConnectedTask')->getMockForAbstractClass();

        $this->assertInstanceOf('Accompli\Deployment\Connection\ConnectionAdapterInterface', $task->ensureConnection($hostMock));
    }

    /**
     * Tests if AbstractConnectedTask::ensureConnection throws a ConnectionException when the connection adapter is not connected and not able to connect.
     *
     * @expectedException        Accompli\Exception\ConnectionException
     * @expectedExceptionMessage Could not connect to "accompli.deployment.net" through "test".
     */
    public function testEnsureConnectionThrowsConnectionExceptionWhenNotConnectedAndNotAbleToConnect()
    {
        $connectionAdapterMock = $this->getMockBuilder('Accompli\Deployment\Connection\ConnectionAdapterInterface')->getMock();
        $connectionAdapterMock->expects($this->once())->method('isConnected')->willReturn(false);
        $connectionAdapterMock->expects($this->once())->method('connect')->willReturn(false);

        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())->method('hasConnection')->willReturn(true);
        $hostMock->expects($this->once())->method('getConnection')->willReturn($connectionAdapterMock);
        $hostMock->expects($this->once())->method('getHostname')->willReturn('accompli.deployment.net');
        $hostMock->expects($this->once())->method('getConnectionType')->willReturn('test');

        $task = $this->getMockBuilder('Accompli\Task\AbstractConnectedTask')->getMockForAbstractClass();
        $task->ensureConnection($hostMock);
    }

    /**
     * Tests if AbstractConnectedTask::ensureConnection throws a ConnectionException when a connection adapter is not available on the Host instance.
     *
     * @expectedException        Accompli\Exception\ConnectionException
     * @expectedExceptionMessage No connection adapter of type "test" found on host.
     */
    public function testEnsureConnectionThrowsConnectionExceptionWhenNoConnectionAdapterAvailable()
    {
        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())->method('hasConnection')->willReturn(false);
        $hostMock->expects($this->once())->method('getConnectionType')->willReturn('test');

        $task = $this->getMockBuilder('Accompli\Task\AbstractConnectedTask')->getMockForAbstractClass();
        $task->ensureConnection($hostMock);
    }
}
