<?php

namespace Accompli\Test;

use Accompli\EventDispatcher\EventDispatcher;
use PHPUnit_Framework_TestCase;
use Symfony\Component\EventDispatcher\Event;

/**
 * EventDispatcherTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class EventDispatcherTest extends PHPUnit_Framework_TestCase
{
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

        $lastDispatchedEvent = $eventDispatcher->getLastDispatchedEvent();
        $this->assertSame($event, $lastDispatchedEvent);
    }

    /**
     * Tests if EventDispatcher::getLastDispatchedEvent always returns an event instance even when no event instance has been dispatched.
     */
    public function testGetLastDispatchedAlwaysReturnsEventInstanceAfterDispatch()
    {
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->dispatch('test');

        $lastDispatchedEvent = $eventDispatcher->getLastDispatchedEvent();
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\Event', $lastDispatchedEvent);
    }
}
