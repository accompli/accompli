<?php

namespace Accompli\Test\EventDispatcher\Subscriber;

use Accompli\AccompliEvents;
use Accompli\EventDispatcher\Event\LogEvent;
use Accompli\EventDispatcher\Subscriber\LogSubscriber;
use Accompli\Task\CreateWorkspaceTask;
use PHPUnit_Framework_TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * LogSubscriberTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class LogSubscriberTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if LogSubscriber::getSubscribedEvents returns an array with a AccompliEvents::LOG key.
     */
    public function testGetSubscribedEvents()
    {
        $this->assertInternalType('array', LogSubscriber::getSubscribedEvents());
        $this->assertArrayHasKey(AccompliEvents::LOG, LogSubscriber::getSubscribedEvents());
    }

    /**
     * Tests if constructing a new LogSubscriber instance sets the logger property.
     */
    public function testConstruct()
    {
        $loggerMock = $this->getMockBuilder(LoggerInterface::class)
                ->getMock();

        $logSubscriber = new LogSubscriber($loggerMock);

        $this->assertAttributeSame($loggerMock, 'logger', $logSubscriber);
    }

    /**
     * Tests if LogSubscriber::onLogEvent calls the log method on the logger.
     *
     * @depends testConstruct
     */
    public function testOnLogEvent()
    {
        $eventSubscriberMock = $this->getMockBuilder(EventSubscriberInterface::class)
                ->getMock();

        $logEventMock = $this->getMockBuilder(LogEvent::class)
                ->disableOriginalConstructor()
                ->getMock();
        $logEventMock->expects($this->once())
                ->method('getLevel')
                ->willReturn(LogLevel::DEBUG);
        $logEventMock->expects($this->once())
                ->method('getMessage')
                ->willReturn('Test');
        $logEventMock->expects($this->once())
                ->method('getEventNameContext')
                ->willReturn('accompli.test');
        $logEventMock->expects($this->once())
                ->method('getEventSubscriberContext')
                ->willReturn($eventSubscriberMock);
        $logEventMock->expects($this->once())
                ->method('getContext')
                ->willReturn(array());

        $loggerMock = $this->getMockBuilder(LoggerInterface::class)
                ->getMock();
        $loggerMock->expects($this->once())
                ->method('log')
                ->with(
                    $this->equalTo(LogLevel::DEBUG),
                    $this->equalTo('Test'),
                    $this->equalTo(array('event.name' => 'accompli.test', 'event.task.name' => get_class($eventSubscriberMock)))
                );

        $logSubscriber = new LogSubscriber($loggerMock);
        $logSubscriber->onLogEvent($logEventMock);
    }

    /**
     * Tests if LogSubscriber::onLogEvent calls the log method on the logger.
     *
     * @depends testConstruct
     */
    public function testOnLogEventWithNamespacedEventSubscriber()
    {
        $eventSubscriber = new CreateWorkspaceTask();

        $logEventMock = $this->getMockBuilder(LogEvent::class)
                ->disableOriginalConstructor()
                ->getMock();
        $logEventMock->expects($this->once())
                ->method('getLevel')
                ->willReturn(LogLevel::DEBUG);
        $logEventMock->expects($this->once())
                ->method('getMessage')
                ->willReturn('Test');
        $logEventMock->expects($this->once())
                ->method('getEventNameContext')
                ->willReturn('accompli.test');
        $logEventMock->expects($this->once())
                ->method('getEventSubscriberContext')
                ->willReturn($eventSubscriber);
        $logEventMock->expects($this->once())
                ->method('getContext')
                ->willReturn(array());

        $loggerMock = $this->getMockBuilder(LoggerInterface::class)
                ->getMock();
        $loggerMock->expects($this->once())
                ->method('log')
                ->with(
                    $this->equalTo(LogLevel::DEBUG),
                    $this->equalTo('Test'),
                    $this->equalTo(array('event.name' => 'accompli.test', 'event.task.name' => 'CreateWorkspaceTask'))
                );

        $logSubscriber = new LogSubscriber($loggerMock);
        $logSubscriber->onLogEvent($logEventMock);
    }
}
