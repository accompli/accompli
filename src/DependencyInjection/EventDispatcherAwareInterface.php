<?php

namespace Accompli\DependencyInjection;

use Accompli\EventDispatcher\EventDispatcherInterface;

/**
 * EventDispatcherAwareInterface.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
interface EventDispatcherAwareInterface
{
    /**
     * Sets the event dispatcher.
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher);
}
