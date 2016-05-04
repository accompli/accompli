<?php

namespace Accompli\EventDispatcher\Subscriber;

use Accompli\AccompliEvents;
use Accompli\Console\Logger\ConsoleLoggerInterface;
use Accompli\DataCollector\DataCollectorInterface;
use Accompli\DataCollector\EventDataCollector;
use Accompli\Report\DeployReport;
use Accompli\Report\InstallReport;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * GenerateReportSubscriber.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class GenerateReportSubscriber implements EventSubscriberInterface
{
    /**
     * The logger instance.
     *
     * @var ConsoleLoggerInterface
     */
    private $logger;

    /**
     * The EventDataCollector instance.
     *
     * @var EventDataCollector
     */
    private $eventDataCollector;

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
        return array(
            AccompliEvents::INSTALL_COMMAND_COMPLETE => array(
                array('onInstallCommandCompletedOutputReport', 0),
            ),
            AccompliEvents::DEPLOY_COMMAND_COMPLETE => array(
                array('onDeployCommandCompletedOutputReport', 0),
            ),
        );
    }

    /**
     * Constructs a new GenerateReportSubscriber instance.
     *
     * @param ConsoleLoggerInterface   $logger
     * @param EventDataCollector       $eventDataCollector
     * @param DataCollectorInterface[] $dataCollectors
     */
    public function __construct(ConsoleLoggerInterface $logger, EventDataCollector $eventDataCollector, array $dataCollectors)
    {
        $this->logger = $logger;
        $this->eventDataCollector = $eventDataCollector;
        $this->dataCollectors = $dataCollectors;
    }

    /**
     * Generates an installation report.
     */
    public function onInstallCommandCompletedOutputReport()
    {
        $report = new InstallReport($this->eventDataCollector, $this->dataCollectors);
        $report->generate($this->logger);
    }

    /**
     * Generates a deployment report.
     */
    public function onDeployCommandCompletedOutputReport()
    {
        $report = new DeployReport($this->eventDataCollector, $this->dataCollectors);
        $report->generate($this->logger);
    }
}
