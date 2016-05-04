<?php

namespace Accompli\DataCollector;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * DataCollectorInterface.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
interface DataCollectorInterface
{
    /**
     * Collect data from the event.
     *
     * @param Event  $event
     * @param string $eventName
     */
    public function collect(Event $event, $eventName);

    /**
     * Returns the collected data (filtered by verbosity).
     *
     * @param int $verbosity
     *
     * @return array
     */
    public function getData($verbosity = OutputInterface::VERBOSITY_NORMAL);
}
