<?php

namespace Accompli\EventDispatcher\Event;

use Exception;
use Symfony\Component\EventDispatcher\Event;

/**
 * FailedEvent.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class FailedEvent extends Event
{
    /**
     * The last event name dispatched before the failure occured.
     *
     * @var string
     */
    private $eventName;

    /**
     * The last event dispatched before the failure occurred.
     *
     * @var Event
     */
    private $event;

    /**
     * The exception that occurred during the last event.
     *
     * @var Exception|null
     */
    private $exception;

    /**
     * Constructs a new FailedEvent.
     *
     * @param string    $eventName
     * @param Event     $event
     * @param Exception $exception
     */
    public function __construct($eventName, Event $event, Exception $exception = null)
    {
        $this->eventName = $eventName;
        $this->event = $event;
        $this->exception = $exception;
    }

    /**
     * Returns the last event name that was dispatched.
     *
     * @return string
     */
    public function getLastEventName()
    {
        return $this->eventName;
    }

    /**
     * Returns the last event instance that was dispatched.
     *
     * @return Event
     */
    public function getLastEvent()
    {
        return $this->event;
    }

    /**
     * Returns the exception that occurred during the last event.
     *
     * @return Exception|null
     */
    public function getException()
    {
        return $this->exception;
    }
}
