<?php

namespace Accompli\Test\Exception;

use Accompli\Exception\TaskRuntimeException;
use PHPUnit_Framework_TestCase;

/**
 * TaskRuntimeExceptionTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class TaskRuntimeExceptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if constructing a new TaskRuntimeException sets the properties.
     */
    public function testConstruct()
    {
        $taskMock = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventSubscriberInterface')
                ->getMock();

        $exception = new TaskRuntimeException('Test exception', $taskMock);

        $this->assertAttributeSame('Test exception', 'message', $exception);
        $this->assertAttributeSame($taskMock, 'task', $exception);
    }

    /**
     * Tests if TaskRuntimeException::getTask returns the task instance set in the constructor.
     *
     * @depends testConstruct
     */
    public function testGetTask()
    {
        $taskMock = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventSubscriberInterface')
                ->getMock();

        $exception = new TaskRuntimeException('Test exception', $taskMock);

        $this->assertSame($taskMock, $exception->getTask());
    }
}
