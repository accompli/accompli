<?php

namespace Accompli\EventDispatcher;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as BaseEventDispatcherInterface;

/**
 * EventDispatcherInterface.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
interface EventDispatcherInterface extends BaseEventDispatcherInterface
{
    /**
     * Returns the last dispatched Event instance.
     *
     * @return Event
     */
    public function getLastDispatchedEvent();
}
