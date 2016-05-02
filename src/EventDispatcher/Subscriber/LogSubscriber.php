<?php

namespace Accompli\EventDispatcher\Subscriber;

use Accompli\AccompliEvents;
use Accompli\EventDispatcher\Event\LogEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * LogSubscriber.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class LogSubscriber implements EventSubscriberInterface
{
    /**
     * The logger instance.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            AccompliEvents::LOG => array(
                array('onLogEvent', 0),
            ),
        );
    }

    /**
     * Constructs a new LogSubscriber instance.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Logs the LogEvent to the logger instance.
     *
     * @param LogEvent $event
     */
    public function onLogEvent(LogEvent $event)
    {
        $context = $event->getContext();
        $context['event.name'] = $event->getEventNameContext();

        $eventSubscriberClassName = get_class($event->getEventSubscriberContext());
        if (strrpos($eventSubscriberClassName, '\\') !== false) {
            $eventSubscriberClassName = substr($eventSubscriberClassName, strrpos($eventSubscriberClassName, '\\') + 1);
        }
        $context['event.task.name'] = $eventSubscriberClassName;

        $this->logger->log($event->getLevel(), $event->getMessage(), $context);
    }
}
