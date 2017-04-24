<?php

namespace Accompli\Test\EventDispatcher;

use Accompli\AccompliEvents;
use Accompli\Deployment\Host;
use Accompli\EventDispatcher\Event\LogEvent;
use Accompli\EventDispatcher\EventDispatcher;
use Accompli\Test\Mock\EventListenerSubscriberMock;
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

    /**
     * Tests subscribing tagged subsribers.
     */
    public function testSubscribeTaggedSubscribers()
    {
        $eventDispatcher = new EventDispatcher();
        $eventSubscriber = new EventListenerSubscriberMock();
        $eventDispatcher->addTaggedSubscriber($eventSubscriber, array('foo', 'bar'));

        $this->assertAttributeEquals(array(array('subscriber' => $eventSubscriber, 'tags' => array('foo', 'bar'))), 'tagMap', $eventDispatcher);
        $this->assertFalse($eventDispatcher->hasListeners('subscribed_event'));
    }

    /**
     * Tests configuring the tagged subscribers.
     *
     * @dataProvider provideConfigureTaggedSubscribersTestData
     *
     * @param array $tags
     * @param bool  $hostHasTag
     */
    public function testConfigureTaggedSubscribers(array $tags, $hostHasTag)
    {
        $eventDispatcher = new EventDispatcher();
        $eventSubscriber = new EventListenerSubscriberMock();
        $eventDispatcher->addTaggedSubscriber($eventSubscriber, $tags);

        $hostMock = $this->getMockBuilder(Host::class)
            ->disableOriginalConstructor()
            ->getMock();
        $hostMock->expects($this->any())
            ->method('hasTag')
            ->willReturn($hostHasTag);

        $eventDispatcher->configureTaggedSubscribers($hostMock);

        $this->assertEquals($hostHasTag, $eventDispatcher->hasListeners('subscribed_event'));
    }

    /**
     * Gets test data for testConfigureTaggedSubscribersTestData.
     *
     * @return array
     */
    public function provideConfigureTaggedSubscribersTestData()
    {
        return array(
            array(array('foo', 'bar'), false),
            array(array('foo', 'bar'), true),
        );
    }
}
