<?php

namespace Accompli\EventDispatcher\Event;

use InvalidArgumentException;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * LogEvent.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class LogEvent extends Event
{
    /**
     * The log level.
     *
     * @var string
     */
    private $level;

    /**
     * The message to log.
     *
     * @var string
     */
    private $message;

    /**
     * The name of the event as context for the log message.
     *
     * @var string
     */
    private $eventNameContext;

    /**
     * The event subscriber instance as context for the log message.
     *
     * @var Event
     */
    private $eventSubscriberContext;

    /**
     * The array with context for the log message.
     *
     * @var array
     */
    private $context;

    /**
     * Constructs a new LogEvent instance.
     *
     * @param string                        $level
     * @param string                        $message
     * @param string                        $eventNameContext
     * @param EventSubscriberInterface|null $eventSubscriberContext
     * @param array                         $context
     */
    public function __construct($level, $message, $eventNameContext, EventSubscriberInterface $eventSubscriberContext = null, array $context = array())
    {
        if ($this->isValidLogLevel($level) === false) {
            throw new InvalidArgumentException(sprintf('The provided level "%s" is not a valid log level.', $level));
        }

        $this->level = $level;
        $this->message = $message;
        $this->eventNameContext = $eventNameContext;
        $this->eventSubscriberContext = $eventSubscriberContext;
        $this->context = $context;
    }

    /**
     * Returns true if the level is a valid log level.
     *
     * @param string $level
     *
     * @return bool
     */
    private function isValidLogLevel($level)
    {
        return in_array($level, array(
                LogLevel::EMERGENCY,
                LogLevel::ALERT,
                LogLevel::CRITICAL,
                LogLevel::ERROR,
                LogLevel::WARNING,
                LogLevel::NOTICE,
                LogLevel::INFO,
                LogLevel::DEBUG,
            )
        );
    }

    /**
     * Returns the log level.
     *
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Returns the message to log.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Returns the name of the event as context for the log message.
     *
     * @return string
     */
    public function getEventNameContext()
    {
        return $this->eventNameContext;
    }

    /**
     * Returns the event subscriber instance as context for the log message.
     *
     * @return EventSubscriberInterface
     */
    public function getEventSubscriberContext()
    {
        return $this->eventSubscriberContext;
    }

    /**
     * Returns the array with context for the log message.
     *
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }
}
