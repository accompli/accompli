<?php

namespace Accompli\EventDispatcher;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher as BaseEventDispatcher;

/**
 * EventDispatcher.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class EventDispatcher extends BaseEventDispatcher implements EventDispatcherInterface
{
    /**
     * The instance of the last dispatched event.
     *
     * @var Event
     */
    private $lastDispatchedEvent;

    /**
     * {@inheritdoc}
     */
    public function dispatch($eventName, Event $event = null)
    {
        if (($event instanceof Event) === false) {
            $event = new Event();
        }

        $this->lastDispatchedEvent = $event;

        return parent::dispatch($eventName, $event);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastDispatchedEvent()
    {
        return $this->lastDispatchedEvent;
    }
}
