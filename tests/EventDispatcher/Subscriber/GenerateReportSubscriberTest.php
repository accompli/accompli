<?php

namespace Accompli\Test\EventDispatcher\Subscriber;

use Accompli\AccompliEvents;
use Accompli\EventDispatcher\Subscriber\GenerateReportSubscriber;
use PHPUnit_Framework_TestCase;

/**
 * GenerateReportSubscriberTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class GenerateReportSubscriberTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if GenerateReportSubscriber::getSubscribedEvents returns an array with AccompliEvent command complete keys.
     */
    public function testGetSubscribedEvents()
    {
        $this->assertInternalType('array', GenerateReportSubscriber::getSubscribedEvents());
        $this->assertArrayHasKey(AccompliEvents::INSTALL_COMMAND_COMPLETE, GenerateReportSubscriber::getSubscribedEvents());
        $this->assertArrayHasKey(AccompliEvents::DEPLOY_COMMAND_COMPLETE, GenerateReportSubscriber::getSubscribedEvents());
    }

    /**
     * Tests if constructing a new GenerateReportSubscriber instance sets the properties.
     */
    public function testConstruct()
    {
        $loggerMock = $this->getMockBuilder('Accompli\Console\Logger\ConsoleLoggerInterface')
                ->getMock();

        $eventDataCollectorMock = $this->getMockBuilder('Accompli\DataCollector\EventDataCollector')
                ->getMock();

        $subscriber = new GenerateReportSubscriber($loggerMock, $eventDataCollectorMock, array());

        $this->assertAttributeSame($loggerMock, 'logger', $subscriber);
        $this->assertAttributeSame($eventDataCollectorMock, 'eventDataCollector', $subscriber);
        $this->assertAttributeSame(array(), 'dataCollectors', $subscriber);
    }
}
