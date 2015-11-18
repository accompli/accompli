<?php

namespace Accompli\Test;

use Accompli\AccompliEvents;
use Accompli\EventDispatcher\Subscriber\LogSubscriber;
use PHPUnit_Framework_TestCase;
use Psr\Log\LogLevel;

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
     * Tests if LogSubscriber::setLogger sets the logger property.
     */
    public function testSetLogger()
    {
        $loggerMock = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();

        $logSubscriber = new LogSubscriber();
        $logSubscriber->setLogger($loggerMock);

        $this->assertAttributeSame($loggerMock, 'logger', $logSubscriber);
    }

    /**
     * Tests if LogSubscriber::onLogEvent calls the log method on the logger.
     *
     * @depends testSetLogger
     */
    public function testOnLogEvent()
    {
        $logEventMock = $this->getMockBuilder('Accompli\EventDispatcher\Event\LogEvent')
                ->disableOriginalConstructor()
                ->getMock();
        $logEventMock->expects($this->once())->method('getLevel')->willReturn(LogLevel::DEBUG);
        $logEventMock->expects($this->once())->method('getMessage')->willReturn('Test');
        $logEventMock->expects($this->exactly(2))->method('getEventContext')->willReturn($logEventMock);
        $logEventMock->expects($this->once())->method('getContext')->willReturn(array());

        $loggerMock = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();
        $loggerMock->expects($this->once())
                ->method('log')
                ->with(
                    $this->equalTo(LogLevel::DEBUG),
                    $this->equalTo('[{eventContext}] Test'),
                    $this->equalTo(array('eventContext' => get_class($logEventMock), 'event' => $logEventMock))
                );

        $logSubscriber = new LogSubscriber();
        $logSubscriber->setLogger($loggerMock);
        $logSubscriber->onLogEvent($logEventMock);
    }
}
