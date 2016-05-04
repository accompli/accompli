<?php

namespace Accompli\DataCollector;

use Accompli\EventDispatcher\Event\FailedEvent;
use Accompli\EventDispatcher\Event\LogEvent;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * EventDataCollector.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class EventDataCollector implements DataCollectorInterface
{
    /**
     * The amount of LogEvent instances counted of a specific LogLevel.
     *
     * @var array
     */
    private $logLevelCount = array(
        LogLevel::EMERGENCY => 0,
        LogLevel::ALERT => 0,
        LogLevel::CRITICAL => 0,
        LogLevel::ERROR => 0,
        LogLevel::WARNING => 0,
        LogLevel::NOTICE => 0,
        LogLevel::INFO => 0,
        LogLevel::DEBUG => 0,
    );

    /**
     * The amount of FailedEvent instances counted.
     *
     * @var int
     */
    private $failedEventCount = 0;

    /**
     * {@inheritdoc}
     */
    public function collect(Event $event, $eventName)
    {
        if ($event instanceof LogEvent) {
            ++$this->logLevelCount[$event->getLevel()];
        } elseif ($event instanceof FailedEvent) {
            ++$this->failedEventCount;
        }
    }

    /**
     * Returns true if LogEvent instances are counted for a certain LogLevel.
     *
     * @param string $logLevel
     *
     * @return bool
     */
    public function hasCountedLogLevel($logLevel)
    {
        if (isset($this->logLevelCount[$logLevel])) {
            return $this->logLevelCount[$logLevel] > 0;
        }

        return false;
    }

    /**
     * Returns true if FailedEvent instances are counted.
     *
     * @return bool
     */
    public function hasCountedFailedEvents()
    {
        return $this->failedEventCount > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getData($verbosity = OutputInterface::VERBOSITY_NORMAL)
    {
        return array();
    }
}
