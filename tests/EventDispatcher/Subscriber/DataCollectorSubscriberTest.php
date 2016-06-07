<?php

namespace Accompli\Test\EventDispatcher\Subscriber;

use Accompli\DataCollector\DataCollectorInterface;
use Accompli\EventDispatcher\Subscriber\DataCollectorSubscriber;
use PHPUnit_Framework_TestCase;
use Symfony\Component\EventDispatcher\Event;

/**
 * DataCollectorSubscriberTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class DataCollectorSubscriberTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if DataCollectorSubscriber::getSubscribedEvents returns an array with AccompliEvents keys.
     */
    public function testGetSubscribedEvents()
    {
        $this->assertInternalType('array', DataCollectorSubscriber::getSubscribedEvents());
    }

    /**
     * Tests if constructing a new DataCollectorSubscriber instance sets the properties.
     */
    public function testConstruct()
    {
        $dataSubscriber = new DataCollectorSubscriber(array());

        $this->assertAttributeSame(array(), 'dataCollectors', $dataSubscriber);
    }

    /**
     * Tests if DataCollectorSubscriber::addDataCollector adds a data collector instance to the data collectors.
     *
     * @depends testConstruct
     */
    public function testAddDataCollector()
    {
        $dataCollectorMock = $this->getMockBuilder(DataCollectorInterface::class)
                ->getMock();

        $dataSubscriber = new DataCollectorSubscriber(array());
        $dataSubscriber->addDataCollector($dataCollectorMock);

        $this->assertAttributeSame(array($dataCollectorMock), 'dataCollectors', $dataSubscriber);
    }

    /**
     * Tests if DataCollectorSubscriber::onEvent calls the collect method on all data collector instances.
     *
     * @depends testConstruct
     */
    public function testOnEvent()
    {
        $dataCollectorMock = $this->getMockBuilder(DataCollectorInterface::class)
                ->getMock();
        $dataCollectorMock->expects($this->once())
                ->method('collect');

        $dataSubscriber = new DataCollectorSubscriber(array($dataCollectorMock));
        $dataSubscriber->onEvent(new Event(), 'accompli.test');
    }
}
