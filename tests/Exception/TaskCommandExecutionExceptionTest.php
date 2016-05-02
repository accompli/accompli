<?php

namespace Accompli\Test\Exception;

use Accompli\Exception\TaskCommandExecutionException;
use PHPUnit_Framework_TestCase;

/**
 * TaskCommandExecutionExceptionTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class TaskCommandExecutionExceptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if constructing a new TaskCommandExecutionException sets the properties.
     */
    public function testConstruct()
    {
        $processExecutionResultMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutionResult')
                ->disableOriginalConstructor()
                ->getMock();

        $taskMock = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventSubscriberInterface')
                ->getMock();

        $exception = new TaskCommandExecutionException('Test exception', $processExecutionResultMock, $taskMock);

        $this->assertAttributeSame('Test exception', 'message', $exception);
        $this->assertAttributeSame($taskMock, 'task', $exception);
        $this->assertAttributeSame($processExecutionResultMock, 'processExecutionResult', $exception);
    }

    /**
     * Tests if TaskCommandExecutionException::getProcessExecutionResult returns the ProcessExecutionResult instance set in the constructor.
     *
     * @depends testConstruct
     */
    public function testGetProcessExecutionResult()
    {
        $processExecutionResultMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutionResult')
                ->disableOriginalConstructor()
                ->getMock();

        $exception = new TaskCommandExecutionException('Test exception', $processExecutionResultMock);

        $this->assertSame($processExecutionResultMock, $exception->getProcessExecutionResult());
    }
}
