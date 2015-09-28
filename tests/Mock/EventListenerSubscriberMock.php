<?php

namespace Accompli\Test\Mock;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * EventListenerSubscriberMock.
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 */
class EventListenerSubscriberMock implements EventSubscriberInterface
{
    /**
     * {InheritDoc}.
     */
    public static function getSubscribedEvents()
    {
        return array(
            'subscribed_event' => 'eventListener',
        );
    }

    /**
     * Test event listener.
     */
    public function eventListener(Event $event)
    {
    }
}
