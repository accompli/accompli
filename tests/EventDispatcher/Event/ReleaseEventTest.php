<?php

namespace Accompli\Test\EventDispatcher\Event;

use Accompli\EventDispatcher\Event\ReleaseEvent;
use PHPUnit_Framework_TestCase;

/**
 * ReleaseEventTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class ReleaseEventTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if constructing a new ReleaseEvent sets the properties.
     */
    public function testConstruct()
    {
        $releaseMock = $this->getMockBuilder('Accompli\Deployment\Release')
                ->disableOriginalConstructor()
                ->getMock();

        $releaseEvent = new ReleaseEvent($releaseMock);

        $this->assertAttributeSame($releaseMock, 'release', $releaseEvent);
    }

    /**
     * Tests if ReleaseEvent::getRelease returns the same Release instance as during construction of ReleaseEvent.
     *
     * @depends testConstruct
     */
    public function testGetRelease()
    {
        $releaseMock = $this->getMockBuilder('Accompli\Deployment\Release')
                ->disableOriginalConstructor()
                ->getMock();

        $releaseEvent = new ReleaseEvent($releaseMock);

        $this->assertSame($releaseMock, $releaseEvent->getRelease());
    }
}
