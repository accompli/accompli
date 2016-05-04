<?php

namespace Accompli\Test\DataCollector;

use Accompli\AccompliEvents;
use Accompli\DataCollector\ProfilerDataCollector;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * ProfilerDataCollectorTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class ProfilerDataCollectorTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if ProfilerDataCollector::collect sets the start DateTime instance.
     */
    public function testCollectInitialize()
    {
        $dataCollector = new ProfilerDataCollector();
        $dataCollector->collect(new Event(), AccompliEvents::INITIALIZE);

        $this->assertAttributeInstanceOf('DateTime', 'start', $dataCollector);
    }

    /**
     * Test if ProfilerDataCollector::collect sets the end DateTime instance and peak memory usage.
     *
     * @dataProvider provideCommandCompleteEventNames
     *
     * @param string $commandCompleteEventName
     */
    public function testCollectCommandComplete($commandCompleteEventName)
    {
        $dataCollector = new ProfilerDataCollector();
        $dataCollector->collect(new Event(), $commandCompleteEventName);

        $this->assertAttributeInstanceOf('DateTime', 'end', $dataCollector);
        $this->assertAttributeGreaterThan(0, 'peakMemoryUsage', $dataCollector);
    }

    /**
     * Tests if ProfilerDataCollector::getData returns an empty array at normal verbosity.
     */
    public function testGetDataReturnsEmptyArray()
    {
        $dataCollector = new ProfilerDataCollector();

        $this->assertInternalType('array', $dataCollector->getData());
        $this->assertEmpty($dataCollector->getData());
    }

    /**
     * Tests if ProfilerDataCollector::getData returns an array with execution time at verbose verbosity.
     */
    public function testGetDataAtVerbosityVerbose()
    {
        $dataCollector = new ProfilerDataCollector();
        $dataCollector->collect(new Event(), AccompliEvents::INITIALIZE);
        $dataCollector->collect(new Event(), AccompliEvents::INSTALL_COMMAND_COMPLETE);

        $this->assertInternalType('array', $dataCollector->getData(OutputInterface::VERBOSITY_VERBOSE));
        $this->assertArrayHasKey('Execution time', $dataCollector->getData(OutputInterface::VERBOSITY_VERBOSE));
    }

    /**
     * Tests if ProfilerDataCollector::getData returns an array with an unknown execution time when for some reason the event were not collected.
     */
    public function testGetDataWithoutCollectedEventsAtVerbosityVerbose()
    {
        $dataCollector = new ProfilerDataCollector();

        $this->assertInternalType('array', $dataCollector->getData(OutputInterface::VERBOSITY_VERBOSE));
        $this->assertSame(array('Execution time' => 'Unknown'), $dataCollector->getData(OutputInterface::VERBOSITY_VERBOSE));
    }

    /**
     * Tests if ProfilerDataCollector::getData returns an array with execution time and peak memory usage at debug verbosity.
     */
    public function testGetDataAtVerbosityDebug()
    {
        $dataCollector = new ProfilerDataCollector();
        $dataCollector->collect(new Event(), AccompliEvents::INITIALIZE);
        $dataCollector->collect(new Event(), AccompliEvents::INSTALL_COMMAND_COMPLETE);

        $this->assertInternalType('array', $dataCollector->getData(OutputInterface::VERBOSITY_DEBUG));
        $this->assertArrayHasKey('Execution time', $dataCollector->getData(OutputInterface::VERBOSITY_DEBUG));
    }

    /**
     * Returns an array with command complete event names.
     *
     * @return array
     */
    public function provideCommandCompleteEventNames()
    {
        return array(
            array(AccompliEvents::INSTALL_COMMAND_COMPLETE),
            array(AccompliEvents::DEPLOY_COMMAND_COMPLETE),
        );
    }
}
