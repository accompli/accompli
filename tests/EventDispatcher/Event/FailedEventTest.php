<?php

namespace Accompli\Test\EventDispatcher\Event;

use Accompli\EventDispatcher\Event\FailedEvent;
use Exception;
use PHPUnit_Framework_TestCase;
use Symfony\Component\EventDispatcher\Event;

/**
 * FailedEventTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class FailedEventTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if constructing a new FailedEvent sets the properties properly.
     *
     * @dataProvider provideConstructSetsProperties
     *
     * @param string    $eventName
     * @param Event     $event
     * @param Exception $exception
     */
    public function testConstructSetsProperties($eventName, $event, $exception)
    {
        $failedEvent = new FailedEvent($eventName, $event, $exception);

        $this->assertAttributeSame($eventName, 'eventName', $failedEvent);
        $this->assertAttributeSame($event, 'event', $failedEvent);
        $this->assertAttributeSame($exception, 'exception', $failedEvent);
    }

    /**
     * Tests if FailedEvent::getLastEventName returns the same value as during construction of FailedEvent.
     */
    public function testGetLastEventName()
    {
        $failedEvent = new FailedEvent('event', new Event());

        $this->assertSame('event', $failedEvent->getLastEventName());
    }

    /**
     * Tests if FailedEvent::getLastEvent returns the same Event instance as during construction of FailedEvent.
     */
    public function testGetLastEvent()
    {
        $event = new Event();

        $failedEvent = new FailedEvent('event', $event);

        $this->assertSame($event, $failedEvent->getLastEvent());
    }

    /**
     * Tests if FailedEvent::getException returns null when an Exception instance is not provided during construction of FailedEvent.
     */
    public function testGetExceptionReturnsNullByDefault()
    {
        $failedEvent = new FailedEvent('event', new Event());

        $this->assertNull($failedEvent->getException());
    }

    /**
     * Tests if FailedEvent::getException returns the same Exception instance as during construction of FailedEvent.
     */
    public function testGetExceptionReturnsExceptionAddedInConstruct()
    {
        $exception = new Exception();

        $failedEvent = new FailedEvent('event', new Event(), $exception);

        $this->assertSame($exception, $failedEvent->getException());
    }

    /**
     * Returns the test data for @see testConstructSetsProperties.
     *
     * @return array
     */
    public function provideConstructSetsProperties()
    {
        return array(
            array('event', new Event(), null),
            array('event', new Event(), new Exception()),
        );
    }
}
