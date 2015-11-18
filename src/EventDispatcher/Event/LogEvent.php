<?php

namespace Accompli\EventDispatcher\Event;

use InvalidArgumentException;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\Event;

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
     * The event instance as context for the log message.
     *
     * @var Event
     */
    private $eventContext;

    /**
     * The array with context for the log message.
     *
     * @var array
     */
    private $context;

    /**
     * Constructs a new LogEvent.
     *
     * @param string $level
     * @param string $message
     * @param Event  $eventContext
     * @param array  $context
     */
    public function __construct($level, $message, Event $eventContext, array $context = array())
    {
        if ($this->isValidLogLevel($level) === false) {
            throw new InvalidArgumentException(sprintf('The provided level "%s" is not a valid log level.', $level));
        }

        $this->level = $level;
        $this->message = $message;
        $this->eventContext = $eventContext;
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
     * Returns the event instance as context for the log message.
     *
     * @return Event
     */
    public function getEventContext()
    {
        return $this->eventContext;
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
