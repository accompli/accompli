<?php

namespace Accompli\DataCollector;

use Accompli\AccompliEvents;
use DateTime;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * ProfilerDataCollector.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class ProfilerDataCollector implements DataCollectorInterface
{
    /**
     * The start DateTime instance.
     *
     * @var DateTime
     */
    private $start;

    /**
     * The end DateTime instance.
     *
     * @var DateTime
     */
    private $end;

    /**
     * The peak memory usage in bytes.
     *
     * @var int
     */
    private $peakMemoryUsage = 0;

    /**
     * {@inheritdoc}
     */
    public function collect(Event $event, $eventName)
    {
        if ($eventName === AccompliEvents::INITIALIZE) {
            $this->start = new DateTime('now');
        } elseif (in_array($eventName, array(AccompliEvents::INSTALL_COMMAND_COMPLETE, AccompliEvents::DEPLOY_COMMAND_COMPLETE))) {
            $this->end = new DateTime('now');
            $this->peakMemoryUsage = memory_get_peak_usage(true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getData($verbosity = OutputInterface::VERBOSITY_NORMAL)
    {
        $data = array();

        if ($verbosity >= OutputInterface::VERBOSITY_VERBOSE) {
            $executionTime = 'Unknown';
            if ($this->start instanceof DateTime !== false && $this->end instanceof DateTime !== false) {
                $durationInterval = $this->start->diff($this->end);
                $durationInterval->h = $durationInterval->d * 24;
                $durationInterval->i = $durationInterval->h * 60;
                if ($durationInterval->i === 0 && $durationInterval->s === 0) {
                    ++$durationInterval->s;
                }

                $executionTime = $durationInterval->format('%i minute(s) and %s second(s)');
            }

            $data['Execution time'] = $executionTime;
        }

        if ($verbosity >= OutputInterface::VERBOSITY_DEBUG) {
            $data['Peak memory usage'] = round($this->peakMemoryUsage / 1024 / 1024, 2).' MB';
        }

        return $data;
    }
}
