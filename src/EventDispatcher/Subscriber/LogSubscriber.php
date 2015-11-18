<?php

namespace Accompli\EventDispatcher\Subscriber;

use Accompli\AccompliEvents;
use Accompli\EventDispatcher\Event\LogEvent;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * LogSubscriber.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class LogSubscriber implements EventSubscriberInterface, LoggerAwareInterface
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
     * Sets a logger instance.
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
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
        $context['eventContext'] = get_class($event->getEventContext());
        $context['event'] = $event->getEventContext();

        $message = sprintf('[{eventContext}] %s', $event->getMessage());

        $this->logger->log($event->getLevel(), $message, $context);
    }
}
