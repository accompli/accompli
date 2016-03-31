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
     * @param Event     $event
     * @param Exception $exception
     */
    public function testConstructSetsProperties($event, $exception)
    {
        $failedEvent = new FailedEvent($event, $exception);

        $this->assertAttributeSame($event, 'event', $failedEvent);
        $this->assertAttributeSame($exception, 'exception', $failedEvent);
    }

    /**
     * Tests if FailedEvent::getLastEvent returns the same Event instance as during construction of FailedEvent.
     */
    public function testGetLastEvent()
    {
        $event = new Event();

        $failedEvent = new FailedEvent($event);

        $this->assertSame($event, $failedEvent->getLastEvent());
    }

    /**
     * Tests if FailedEvent::getException returns null when an Exception instance is not provided during construction of FailedEvent.
     */
    public function testGetExceptionReturnsNullByDefault()
    {
        $failedEvent = new FailedEvent(new Event());

        $this->assertNull($failedEvent->getException());
    }

    /**
     * Tests if FailedEvent::getException returns the same Exception instance as during construction of FailedEvent.
     */
    public function testGetExceptionReturnsExceptionAddedInConstruct()
    {
        $exception = new Exception();

        $failedEvent = new FailedEvent(new Event(), $exception);

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
            array(new Event(), null),
            array(new Event(), new Exception()),
        );
    }
}
