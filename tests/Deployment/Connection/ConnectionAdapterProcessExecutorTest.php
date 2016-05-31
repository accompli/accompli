<?php

namespace Accompli\Test\Deployment\Connection;

use Accompli\Chrono\Process\ProcessExecutionResult;
use Accompli\Deployment\Connection\ConnectionAdapterInterface;
use Accompli\Deployment\Connection\ConnectionAdapterProcessExecutor;
use PHPUnit_Framework_TestCase;

/**
 * ConnectionAdapterProcessExecutorTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class ConnectionAdapterProcessExecutorTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if constructing a new ConnectionAdapterProcessExecutor instance sets the instance properties.
     */
    public function testConstruct()
    {
        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();

        $processExecutor = new ConnectionAdapterProcessExecutor($connectionAdapterMock);

        $this->assertAttributeSame($connectionAdapterMock, 'connectionAdapter', $processExecutor);
    }

    /**
     * Tests if ConnectionAdapterProcessExecutor::isDirectory returns the expected result.
     *
     * @depends testConstruct
     */
    public function testIsDirectory()
    {
        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();
        $connectionAdapterMock->expects($this->once())
                ->method('isDirectory')
                ->willReturn(true);

        $processExecutor = new ConnectionAdapterProcessExecutor($connectionAdapterMock);

        $this->assertTrue($processExecutor->isDirectory('/test/path'));
    }

    /**
     * Tests if ConnectionAdapterProcessExecutor::execute returns the expected result.
     *
     * @depends testConstruct
     */
    public function testExecute()
    {
        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();
        $connectionAdapterMock->expects($this->once())
                ->method('executeCommand')
                ->willReturn(new ProcessExecutionResult(0, '', ''));

        $processExecutor = new ConnectionAdapterProcessExecutor($connectionAdapterMock);

        $this->assertInstanceOf(ProcessExecutionResult::class, $processExecutor->execute('echo test'));
    }

    /**
     * Tests if ConnectionAdapterProcessExecutor::execute changes the working directory and returns the expected result.
     *
     * @depends testExecute
     */
    public function testExecuteWithWorkingDirectory()
    {
        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();
        $connectionAdapterMock->expects($this->once())
                ->method('getWorkingDirectory')
                ->willReturn('/previous/directory');
        $connectionAdapterMock->expects($this->exactly(2))
                ->method('changeWorkingDirectory')
                ->withConsecutive(
                    array('/test/path'),
                    array('/previous/directory')
                )
                ->willReturn(true);
        $connectionAdapterMock->expects($this->once())
                ->method('executeCommand')
                ->willReturn(new ProcessExecutionResult(0, '', ''));

        $processExecutor = new ConnectionAdapterProcessExecutor($connectionAdapterMock);

        $this->assertInstanceOf(ProcessExecutionResult::class, $processExecutor->execute('echo test', '/test/path'));
    }
}
