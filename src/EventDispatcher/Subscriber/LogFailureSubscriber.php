<?php

namespace Accompli\EventDispatcher\Subscriber;

use Accompli\AccompliEvents;
use Accompli\Chrono\Process\ProcessExecutionResult;
use Accompli\EventDispatcher\Event\FailedEvent;
use Accompli\Exception\TaskCommandExecutionException;
use Accompli\Exception\TaskRuntimeException;
use Exception;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * LogFailureSubscriber.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class LogFailureSubscriber implements EventSubscriberInterface
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
            AccompliEvents::INSTALL_RELEASE_FAILED => array(
                array('onFailedEventLogToLogger', 0),
            ),
            AccompliEvents::DEPLOY_RELEASE_FAILED => array(
                array('onFailedEventLogToLogger', 0),
            ),
            AccompliEvents::ROLLBACK_RELEASE_FAILED => array(
                array('onFailedEventLogToLogger', 0),
            ),
        );
    }

    /**
     * Constructs a new LogFailureSubscriber instance.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Logs the FailedEvent to the logger instance.
     *
     * @param FailedEvent $event
     */
    public function onFailedEventLogToLogger(FailedEvent $event)
    {
        $context = array(
            'event.name' => $event->getLastEventName(),
        );

        $message = 'An unknown error occured.';
        $exception = $event->getException();
        if ($exception instanceof Exception) {
            $message = $exception->getMessage();
        }
        if ($exception instanceof TaskRuntimeException && $exception->getTask() instanceof EventSubscriberInterface) {
            $eventSubscriberClassName = get_class($exception->getTask());
            if (strrpos($eventSubscriberClassName, '\\') !== false) {
                $eventSubscriberClassName = substr($eventSubscriberClassName, strrpos($eventSubscriberClassName, '\\') + 1);
            }
            $context['event.task.name'] = $eventSubscriberClassName;
        }

        $this->logger->log(LogLevel::CRITICAL, $message, $context);
        if ($exception instanceof TaskCommandExecutionException && $exception->getProcessExecutionResult() instanceof ProcessExecutionResult) {
            $context['command.result'] = $exception->getProcessExecutionResult()->getOutput();
            $context['separator'] = "\n=================\n";

            $this->logger->log(LogLevel::DEBUG, "{separator} Command output:{separator}\n{command.result}{separator}", $context);
        }
    }
}
