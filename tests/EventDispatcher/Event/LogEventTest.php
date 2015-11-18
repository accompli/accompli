<?php

namespace Accompli\Test;

use Accompli\EventDispatcher\Event\LogEvent;
use PHPUnit_Framework_TestCase;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\Event;

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
        $event = new Event();

        $logEvent = new LogEvent(LogLevel::DEBUG, 'Test', $event);

        $this->assertAttributeSame(LogLevel::DEBUG, 'level', $logEvent);
        $this->assertAttributeSame('Test', 'message', $logEvent);
        $this->assertAttributeSame($event, 'eventContext', $logEvent);
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
        new LogEvent('invalid', 'Test', new Event());
    }

    /**
     * Tests if LogEvent::getLevel returns the same value as during construction of LogEvent.
     */
    public function testGetLevel()
    {
        $logEvent = new LogEvent(LogLevel::DEBUG, 'Test', new Event());

        $this->assertSame(LogLevel::DEBUG, $logEvent->getLevel());
    }

    /**
     * Tests if LogEvent::getMessage returns the same value as during construction of LogEvent.
     */
    public function testGetMessage()
    {
        $logEvent = new LogEvent(LogLevel::DEBUG, 'Test', new Event());

        $this->assertSame('Test', $logEvent->getMessage());
    }

    /**
     * Tests if LogEvent::getEventContext returns the same value as during construction of LogEvent.
     */
    public function testGetEventContext()
    {
        $event = new Event();

        $logEvent = new LogEvent(LogLevel::DEBUG, 'Test', $event);

        $this->assertSame($event, $logEvent->getEventContext());
    }

    /**
     * Tests if LogEvent::getContext returns the same value as during construction of LogEvent.
     */
    public function testGetContext()
    {
        $logEvent = new LogEvent(LogLevel::DEBUG, 'Test', new Event(), array('key' => 'value'));

        $this->assertSame(array('key' => 'value'), $logEvent->getContext());
    }
}
