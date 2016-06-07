<?php

namespace Accompli\Test\DataCollector;

use Accompli\AccompliEvents;
use Accompli\DataCollector\EventDataCollector;
use Accompli\EventDispatcher\Event\FailedEvent;
use Accompli\EventDispatcher\Event\LogEvent;
use PHPUnit_Framework_TestCase;
use Psr\Log\LogLevel;

/**
 * EventDataCollectorTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class EventDataCollectorTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if EventDataCollector::collect increments the log level count when receiving a LogEvent instance.
     */
    public function testCollectLogEvent()
    {
        $eventMock = $this->getMockBuilder(LogEvent::class)
                ->disableOriginalConstructor()
                ->getMock();
        $eventMock->expects($this->once())
                ->method('getLevel')
                ->willReturn(LogLevel::NOTICE);

        $dataCollector = new EventDataCollector();
        $dataCollector->collect($eventMock, AccompliEvents::LOG);

        $expected = array(
            LogLevel::EMERGENCY => 0,
            LogLevel::ALERT => 0,
            LogLevel::CRITICAL => 0,
            LogLevel::ERROR => 0,
            LogLevel::WARNING => 0,
            LogLevel::NOTICE => 1,
            LogLevel::INFO => 0,
            LogLevel::DEBUG => 0,
        );

        $this->assertAttributeSame($expected, 'logLevelCount', $dataCollector);
    }

    /**
     * Tests if EventDataCollector::collect increments the failed event count when receiving a FailedEvent instance.
     */
    public function testCollectFailedEvent()
    {
        $eventMock = $this->getMockBuilder(FailedEvent::class)
                ->disableOriginalConstructor()
                ->getMock();

        $dataCollector = new EventDataCollector();
        $dataCollector->collect($eventMock, AccompliEvents::INSTALL_RELEASE_FAILED);
    }

    /**
     * Tests if EventDataCollector::hasCountedLogLevel returns false.
     */
    public function testHasCountedLogLevelReturnsFalse()
    {
        $dataCollector = new EventDataCollector();

        $this->assertFalse($dataCollector->hasCountedLogLevel(LogLevel::NOTICE));
    }

    /**
     * Tests if EventDataCollector::hasCountedLogLevel returns false for a non-existing log level.
     */
    public function testHasCountedLogLevelReturnsFalseOnNonExistingLogLevel()
    {
        $dataCollector = new EventDataCollector();

        $this->assertFalse($dataCollector->hasCountedLogLevel('doesnotexist'));
    }

    /**
     * Tests if EventDataCollector::hasCountedLogLevel returns true when a requested log level has been counted.
     */
    public function testHasCountedLogLevelReturnsTrueWhenCountedLogLevels()
    {
        $eventMock = $this->getMockBuilder(LogEvent::class)
                ->disableOriginalConstructor()
                ->getMock();
        $eventMock->expects($this->once())
                ->method('getLevel')
                ->willReturn(LogLevel::NOTICE);

        $dataCollector = new EventDataCollector();
        $dataCollector->collect($eventMock, AccompliEvents::LOG);

        $this->assertTrue($dataCollector->hasCountedLogLevel(LogLevel::NOTICE));
    }

    /**
     * Tests if EventDataCollector::hasCountedFailedEvents returns false.
     */
    public function testHasCountedFailedEventsReturnsFalse()
    {
        $dataCollector = new EventDataCollector();

        $this->assertFalse($dataCollector->hasCountedFailedEvents());
    }

    /**
     * Tests if EventDataCollector::hasCountedFailedEvents returns true when a FailedEvent has been counted.
     */
    public function testHasCountedFailedEventsReturnsTrueWhenCountedFailedEvents()
    {
        $eventMock = $this->getMockBuilder(FailedEvent::class)
                ->disableOriginalConstructor()
                ->getMock();

        $dataCollector = new EventDataCollector();
        $dataCollector->collect($eventMock, AccompliEvents::INSTALL_RELEASE_FAILED);

        $this->assertTrue($dataCollector->hasCountedFailedEvents());
    }

    /**
     * Tests if EventDataCollector::getData returns an empty array.
     */
    public function testGetData()
    {
        $dataCollector = new EventDataCollector();

        $this->assertInternalType('array', $dataCollector->getData());
        $this->assertEmpty($dataCollector->getData());
    }
}
