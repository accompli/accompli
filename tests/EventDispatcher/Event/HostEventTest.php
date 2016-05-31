<?php

namespace Accompli\Test\EventDispatcher\Event;

use Accompli\Deployment\Host;
use Accompli\EventDispatcher\Event\HostEvent;
use PHPUnit_Framework_TestCase;

/**
 * HostEventTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class HostEventTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if constructing a new HostEvent sets the properties.
     */
    public function testConstruct()
    {
        $hostMock = $this->getMockBuilder(Host::class)
                ->disableOriginalConstructor()
                ->getMock();

        $event = new HostEvent($hostMock);

        $this->assertAttributeSame($hostMock, 'host', $event);
    }

    /**
     * Tests if HostEvent::getHost returns the same value as during construction of HostEvent.
     */
    public function testGetHost()
    {
        $hostMock = $this->getMockBuilder(Host::class)
                ->disableOriginalConstructor()
                ->getMock();

        $event = new HostEvent($hostMock);

        $this->assertSame($hostMock, $event->getHost());
    }
}
