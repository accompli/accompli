<?php

namespace Accompli\Test\EventDispatcher\Subscriber;

use Accompli\AccompliEvents;
use Accompli\Chrono\Process\ProcessExecutionResult;
use Accompli\EventDispatcher\Event\FailedEvent;
use Accompli\EventDispatcher\Subscriber\LogFailureSubscriber;
use Accompli\Exception\TaskCommandExecutionException;
use Accompli\Task\CreateWorkspaceTask;
use PHPUnit_Framework_TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * LogFailureSubscriberTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class LogFailureSubscriberTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if LogFailureSubscriber::getSubscribedEvents returns an array with AccompliEvents keys.
     */
    public function testGetSubscribedEvents()
    {
        $this->assertInternalType('array', LogFailureSubscriber::getSubscribedEvents());
        $this->assertArrayHasKey(AccompliEvents::INSTALL_RELEASE_FAILED, LogFailureSubscriber::getSubscribedEvents());
        $this->assertArrayHasKey(AccompliEvents::DEPLOY_RELEASE_FAILED, LogFailureSubscriber::getSubscribedEvents());
        $this->assertArrayHasKey(AccompliEvents::ROLLBACK_RELEASE_FAILED, LogFailureSubscriber::getSubscribedEvents());
    }

    /**
     * Tests if constructing a new LogFailureSubscriber instance sets the logger property.
     */
    public function testConstruct()
    {
        $loggerMock = $this->getMockBuilder(LoggerInterface::class)
                ->getMock();

        $logSubscriber = new LogFailureSubscriber($loggerMock);

        $this->assertAttributeSame($loggerMock, 'logger', $logSubscriber);
    }

    /**
     * Tests if LogFailureSubscriber::onFailedEventLogToLogger calls the log method on the logger.
     *
     * @depends testConstruct
     */
    public function testOnFailedEventLogToLogger()
    {
        $failedEventMock = $this->getMockBuilder(FailedEvent::class)
                ->disableOriginalConstructor()
                ->getMock();
        $failedEventMock->expects($this->once())
                ->method('getLastEventName')
                ->willReturn('accompli.test');

        $loggerMock = $this->getMockBuilder(LoggerInterface::class)
                ->getMock();
        $loggerMock->expects($this->once())
                ->method('log')
                ->with(
                    $this->equalTo(LogLevel::CRITICAL),
                    $this->equalTo('An unknown error occured.'),
                    $this->equalTo(array('event.name' => 'accompli.test'))
                );

        $logSubscriber = new LogFailureSubscriber($loggerMock);
        $logSubscriber->onFailedEventLogToLogger($failedEventMock);
    }

    /**
     * Tests if LogFailureSubscriber::onFailedEventLogToLogger calls the log method on the logger.
     *
     * @depends testConstruct
     */
    public function testOnFailedEventLogToLoggerWithTaskCommandExecutionException()
    {
        $task = new CreateWorkspaceTask();

        $taskCommandExecutionException = new TaskCommandExecutionException('Test exception.', new ProcessExecutionResult(1, 'Command output.', ''), $task);

        $failedEventMock = $this->getMockBuilder(FailedEvent::class)
                ->disableOriginalConstructor()
                ->getMock();
        $failedEventMock->expects($this->once())
                ->method('getLastEventName')
                ->willReturn('accompli.test');
        $failedEventMock->expects($this->once())
                ->method('getException')
                ->willReturn($taskCommandExecutionException);

        $loggerMock = $this->getMockBuilder(LoggerInterface::class)
                ->getMock();
        $loggerMock->expects($this->exactly(2))
                ->method('log')
                ->withConsecutive(
                    array(
                        $this->equalTo(LogLevel::CRITICAL),
                        $this->equalTo('Test exception.'),
                        $this->equalTo(array(
                            'event.name' => 'accompli.test',
                            'event.task.name' => 'CreateWorkspaceTask',
                        )),
                    ),
                    array(
                        $this->equalTo(LogLevel::DEBUG),
                        $this->equalTo("{separator} Command output:{separator}\n{command.result}{separator}"),
                        $this->equalTo(array(
                            'event.name' => 'accompli.test',
                            'event.task.name' => 'CreateWorkspaceTask',
                            'command.result' => 'Command output.',
                            'separator' => "\n=================\n",
                        )),
                    )
                );

        $logSubscriber = new LogFailureSubscriber($loggerMock);
        $logSubscriber->onFailedEventLogToLogger($failedEventMock);
    }
}
