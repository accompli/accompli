<?php

namespace Accompli\Test\EventDispatcher\Event;

use Accompli\EventDispatcher\Event\DeployReleaseEvent;
use PHPUnit_Framework_TestCase;

/**
 * DeployReleaseEventTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class DeployReleaseEventTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if constructing a new DeployReleaseEvent sets the properties.
     */
    public function testConstruct()
    {
        $releaseMock = $this->getMockBuilder('Accompli\Deployment\Release')
                ->disableOriginalConstructor()
                ->getMock();

        $deployReleaseEvent = new DeployReleaseEvent($releaseMock);

        $this->assertAttributeSame($releaseMock, 'release', $deployReleaseEvent);
        $this->assertAttributeSame(null, 'currentRelease', $deployReleaseEvent);
    }

    /**
     * Tests if constructing a new DeployReleaseEvent sets the properties.
     *
     * @depends testConstruct
     */
    public function testConstructWithCurrentRelease()
    {
        $releaseMock = $this->getMockBuilder('Accompli\Deployment\Release')
                ->disableOriginalConstructor()
                ->getMock();

        $currentReleaseMock = $this->getMockBuilder('Accompli\Deployment\Release')
                ->disableOriginalConstructor()
                ->getMock();

        $deployReleaseEvent = new DeployReleaseEvent($releaseMock, $currentReleaseMock);

        $this->assertAttributeSame($releaseMock, 'release', $deployReleaseEvent);
        $this->assertAttributeSame($currentReleaseMock, 'currentRelease', $deployReleaseEvent);
    }

    /**
     * Tests if DeployReleaseEvent::getCurrentRelease returns the same Release instance as during construction of DeployReleaseEvent.
     *
     * @depends testConstructWithCurrentRelease
     */
    public function testGetCurrentRelease()
    {
        $releaseMock = $this->getMockBuilder('Accompli\Deployment\Release')
                ->disableOriginalConstructor()
                ->getMock();

        $currentReleaseMock = $this->getMockBuilder('Accompli\Deployment\Release')
                ->disableOriginalConstructor()
                ->getMock();

        $deployReleaseEvent = new DeployReleaseEvent($releaseMock, $currentReleaseMock);

        $this->assertSame($currentReleaseMock, $deployReleaseEvent->getCurrentRelease());
    }
}
