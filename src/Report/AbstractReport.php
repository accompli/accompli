<?php

namespace Accompli\Report;

use Accompli\Console\Helper\TitleBlock;
use Accompli\Console\Logger\ConsoleLoggerInterface;
use Accompli\DataCollector\DataCollectorInterface;
use Accompli\DataCollector\EventDataCollector;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Helper\Table;

/**
 * AbstractReport.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
abstract class AbstractReport
{
    /**
     * The array with messages used indicate successfulness of a command.
     *
     * @var array
     */
    protected $messages = array(
        TitleBlock::STYLE_SUCCESS => '',
        TitleBlock::STYLE_ERRORED_SUCCESS => '',
        TitleBlock::STYLE_FAILURE => '',
    );

    /**
     * The EventDataCollector instance.
     *
     * @var EventDataCollector
     */
    private $eventDataCollector;

    /**
     * The array with data collectors.
     *
     * @var DataCollectorInterface[]
     */
    private $dataCollectors;

    /**
     * Constructs a new instance.
     *
     * @param EventDataCollector $eventDataCollector
     * @param array              $dataCollectors
     */
    public function __construct(EventDataCollector $eventDataCollector, array $dataCollectors)
    {
        $this->eventDataCollector = $eventDataCollector;
        $this->dataCollectors = $dataCollectors;
    }

    /**
     * Generates the output for the report.
     *
     * @param ConsoleLoggerInterface $logger
     */
    public function generate(ConsoleLoggerInterface $logger)
    {
        $logLevel = LogLevel::NOTICE;
        $style = TitleBlock::STYLE_SUCCESS;
        if ($this->eventDataCollector->hasCountedFailedEvents()) {
            $logLevel = LogLevel::ERROR;
            $style = TitleBlock::STYLE_FAILURE;
        } elseif ($this->eventDataCollector->hasCountedLogLevel(LogLevel::EMERGENCY) || $this->eventDataCollector->hasCountedLogLevel(LogLevel::ALERT) || $this->eventDataCollector->hasCountedLogLevel(LogLevel::CRITICAL) || $this->eventDataCollector->hasCountedLogLevel(LogLevel::ERROR) || $this->eventDataCollector->hasCountedLogLevel(LogLevel::WARNING)) {
            $logLevel = LogLevel::WARNING;
            $style = TitleBlock::STYLE_ERRORED_SUCCESS;
        }

        $output = $logger->getOutput($logLevel);

        $titleBlock = new TitleBlock($output, $this->messages[$style], $style);
        $titleBlock->render();

        $dataCollectorsData = array();
        foreach ($this->dataCollectors as $dataCollector) {
            $data = $dataCollector->getData($logger->getVerbosity());
            foreach ($data as $label => $value) {
                $dataCollectorsData[] = array($label, $value);
            }
        }

        $table = new Table($output);
        $table->setRows($dataCollectorsData);
        $table->setStyle('symfony-style-guide');
        $table->render();

        $output->writeln('');
    }
}
