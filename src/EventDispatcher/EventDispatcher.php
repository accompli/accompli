<?php

namespace Accompli\EventDispatcher;

use Accompli\AccompliEvents;
use Accompli\Deployment\Host;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher as BaseEventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * EventDispatcher.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class EventDispatcher extends BaseEventDispatcher implements EventDispatcherInterface
{
    /**
     * The name of the last dispatched event.
     *
     * @var string
     */
    private $lastDispatchedEventName;

    /**
     * The instance of the last dispatched event.
     *
     * @var Event
     */
    private $lastDispatchedEvent;

    /**
     * Mapping of subscribers and tags.
     *
     * @var array
     */
    private $tagMap = array();

    /**
     * {@inheritdoc}
     */
    public function dispatch($eventName, Event $event = null)
    {
        if (($event instanceof Event) === false) {
            $event = new Event();
        }

        if ($eventName !== AccompliEvents::LOG) {
            $this->lastDispatchedEventName = $eventName;
            $this->lastDispatchedEvent = $event;
        }

        return parent::dispatch($eventName, $event);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastDispatchedEventName()
    {
        return $this->lastDispatchedEventName;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastDispatchedEvent()
    {
        return $this->lastDispatchedEvent;
    }

    /**
     * Adds a tagged subscriber (ie. a task that will run on hosts with the same tag only).
     *
     * @param EventSubscriberInterface $subscriber
     * @param array                    $tags
     */
    public function addTaggedSubscriber(EventSubscriberInterface $subscriber, array $tags)
    {
        $this->tagMap[] = array('subscriber' => $subscriber, 'tags' => $tags);
    }

    /**
     * Configure tagged subscribers for $host.
     *
     * @param Host $host
     */
    public function configureTaggedSubscribers(Host $host)
    {
        foreach ($this->tagMap as $taggedSubscriber) {
            $this->removeSubscriber($taggedSubscriber['subscriber']);
            foreach ($taggedSubscriber['tags'] as $tag) {
                if ($host->hasTag($tag)) {
                    $this->addSubscriber($taggedSubscriber['subscriber']);
                    break;
                }
            }
        }
    }
}
