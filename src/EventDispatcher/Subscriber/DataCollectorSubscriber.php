<?php

namespace Accompli\EventDispatcher\Subscriber;

use Accompli\AccompliEvents;
use Accompli\DataCollector\DataCollectorInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * DataCollectorSubscriber.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class DataCollectorSubscriber implements EventSubscriberInterface
{
    /**
     * The array with DataCollectorInterface instances.
     *
     * @var DataCollectorInterface[]
     */
    private $dataCollectors;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        $subscribedEvents = array();
        foreach (AccompliEvents::getEventNames() as $eventName) {
            $subscribedEvents[$eventName] = array(
                array('onEvent', 0),
            );
        }

        return $subscribedEvents;
    }

    /**
     * Constructs a new DataCollectorSubscriber instance.
     *
     * @param DataCollectorInterface[] $dataCollectors
     */
    public function __construct(array $dataCollectors = array())
    {
        $this->dataCollectors = $dataCollectors;
    }

    /**
     * Adds a data collector instance.
     *
     * @param DataCollectorInterface $dataCollector
     */
    public function addDataCollector(DataCollectorInterface $dataCollector)
    {
        $this->dataCollectors[] = $dataCollector;
    }

    /**
     * Calls the collect method on all registered data collectors.
     *
     * @param Event  $event
     * @param string $eventName
     */
    public function onEvent(Event $event, $eventName)
    {
        foreach ($this->dataCollectors as $dataCollector) {
            $dataCollector->collect($event, $eventName);
        }
    }
}
