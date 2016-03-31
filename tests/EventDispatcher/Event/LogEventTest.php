<?php

namespace Accompli\Test\EventDispatcher\Event;

use Accompli\EventDispatcher\Event\LogEvent;
use PHPUnit_Framework_TestCase;
use Psr\Log\LogLevel;

/**
 * LogEventTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class LogEventTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if constructing a new LogEvent sets the properties.
     */
    public function testConstructSetsProperties()
    {
        $eventSubscriberMock = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventSubscriberInterface')->getMock();

        $logEvent = new LogEvent(LogLevel::DEBUG, 'Test', 'accompli.test', $eventSubscriberMock);

        $this->assertAttributeSame(LogLevel::DEBUG, 'level', $logEvent);
        $this->assertAttributeSame('Test', 'message', $logEvent);
        $this->assertAttributeSame('accompli.test', 'eventNameContext', $logEvent);
        $this->assertAttributeSame($eventSubscriberMock, 'eventSubscriberContext', $logEvent);
        $this->assertAttributeSame(array(), 'context', $logEvent);
    }

    /**
     * Tests if constructing a new LogEvent throws an InvalidArgumentException when the log level is invalid.
     *
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage The provided level "invalid" is not a valid log level.
     */
    public function testConstructThrowInvalidArgumentExceptionWhenLogLevelInvalid()
    {
        new LogEvent('invalid', 'Test', 'accompli.test');
    }

    /**
     * Tests if LogEvent::getLevel returns the same value as during construction of LogEvent.
     */
    public function testGetLevel()
    {
        $logEvent = new LogEvent(LogLevel::DEBUG, 'Test', 'accompli.test');

        $this->assertSame(LogLevel::DEBUG, $logEvent->getLevel());
    }

    /**
     * Tests if LogEvent::getMessage returns the same value as during construction of LogEvent.
     */
    public function testGetMessage()
    {
        $logEvent = new LogEvent(LogLevel::DEBUG, 'Test', 'accompli.test');

        $this->assertSame('Test', $logEvent->getMessage());
    }

    /**
     * Tests if LogEvent::getEventNameContext returns the same value as during construction of LogEvent.
     */
    public function testGetEventNameContext()
    {
        $logEvent = new LogEvent(LogLevel::DEBUG, 'Test', 'accompli.test');

        $this->assertSame('accompli.test', $logEvent->getEventNameContext());
    }

    /**
     * Tests if LogEvent::getEventContext returns the same value as during construction of LogEvent.
     */
    public function testGetEventSubscriberContext()
    {
        $eventSubscriberMock = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventSubscriberInterface')->getMock();

        $logEvent = new LogEvent(LogLevel::DEBUG, 'Test', 'accompli.test', $eventSubscriberMock);

        $this->assertSame($eventSubscriberMock, $logEvent->getEventSubscriberContext());
    }

    /**
     * Tests if LogEvent::getContext returns the same value as during construction of LogEvent.
     */
    public function testGetContext()
    {
        $logEvent = new LogEvent(LogLevel::DEBUG, 'Test', 'accompli.test', null, array('key' => 'value'));

        $this->assertSame(array('key' => 'value'), $logEvent->getContext());
    }
}
