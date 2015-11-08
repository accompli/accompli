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
     * The last event dispatched before the failure occurred.
     *
     * @var Event
     */
    private $lastEvent;

    /**
     * The exception that occurred during the last event.
     *
     * @var Exception|null
     */
    private $exception;

    /**
     * Constructs a new FailedEvent.
     *
     * @param Event     $lastEvent
     * @param Exception $exception
     */
    public function __construct(Event $lastEvent, Exception $exception = null)
    {
        $this->lastEvent = $lastEvent;
        $this->exception = $exception;
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
