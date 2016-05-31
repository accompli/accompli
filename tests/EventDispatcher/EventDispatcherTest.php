<?php

namespace Accompli\Test\EventDispatcher;

use Accompli\AccompliEvents;
use Accompli\EventDispatcher\Event\LogEvent;
use Accompli\EventDispatcher\EventDispatcher;
use PHPUnit_Framework_TestCase;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\Event;

/**
 * EventDispatcherTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class EventDispatcherTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if EventDispatcher::getLastDispatchedEventName returns null when no event has been dispatched.
     */
    public function testGetLastDispatchedEventNameReturnsNullWithoutDispatchCalled()
    {
        $eventDispatcher = new EventDispatcher();

        $this->assertNull($eventDispatcher->getLastDispatchedEventName());
    }

    /**
     * Tests if EventDispatcher::getLastDispatchedEventName returns the same event instance that was dispatched.
     */
    public function testGetLastDispatchedEventNameReturnsSameEventInstance()
    {
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->dispatch('test', new Event());

        $this->assertSame('test', $eventDispatcher->getLastDispatchedEventName());
    }

    /**
     * Tests if EventDispatcher::getLastDispatchedEventName does not return a AccompliEvents::LOG event.
     */
    public function testGetLastDispatchedEventNameNeverReturnsLogEvent()
    {
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, 'message', 'eventName'));

        $this->assertNull($eventDispatcher->getLastDispatchedEventName());
    }

    /**
     * Tests if EventDispatcher::getLastDispatchedEvent returns null when no event has been dispatched.
     */
    public function testGetLastDispatchedEventReturnsNullWithoutDispatchCalled()
    {
        $eventDispatcher = new EventDispatcher();

        $this->assertNull($eventDispatcher->getLastDispatchedEvent());
    }

    /**
     * Tests if EventDispatcher::getLastDispatchedEvent returns the same event instance that was dispatched.
     */
    public function testGetLastDispatchedEventReturnsSameEventInstance()
    {
        $event = new Event();

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->dispatch('test', $event);

        $this->assertSame($event, $eventDispatcher->getLastDispatchedEvent());
    }

    /**
     * Tests if EventDispatcher::getLastDispatchedEvent always returns an event instance even when no event instance has been dispatched.
     */
    public function testGetLastDispatchedAlwaysReturnsEventInstanceAfterDispatch()
    {
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->dispatch('test');

        $this->assertInstanceOf(Event::class, $eventDispatcher->getLastDispatchedEvent());
    }

    /**
     * Tests if EventDispatcher::getLastDispatchedEvent does not return a AccompliEvents::LOG event.
     */
    public function testGetLastDispatchedEventNeverReturnsLogEvent()
    {
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, 'message', 'eventName'));

        $this->assertNull($eventDispatcher->getLastDispatchedEvent());
    }
}
