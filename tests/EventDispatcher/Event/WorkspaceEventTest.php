<?php

namespace Accompli\Test\EventDispatcher\Event;

use Accompli\EventDispatcher\Event\WorkspaceEvent;
use PHPUnit_Framework_TestCase;

/**
 * WorkspaceEventTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class WorkspaceEventTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if WorkspaceEvent::setWorkspace sets the workspace property.
     */
    public function testSetWorkspace()
    {
        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();

        $workspaceMock = $this->getMockBuilder('Accompli\Deployment\Workspace')
                ->disableOriginalConstructor()
                ->getMock();

        $workspaceEvent = new WorkspaceEvent($hostMock);
        $workspaceEvent->setWorkspace($workspaceMock);

        $this->assertAttributeSame($workspaceMock, 'workspace', $workspaceEvent);
    }

    /**
     * Tests if WorkspaceEvent::getWorkspace returns the Workspace instance set by WorkspaceEvent::setWorkspace.
     *
     * @depends testSetWorkspace
     */
    public function testGetCurrentRelease()
    {
        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();

        $workspaceMock = $this->getMockBuilder('Accompli\Deployment\Workspace')
                ->disableOriginalConstructor()
                ->getMock();

        $workspaceEvent = new WorkspaceEvent($hostMock);
        $workspaceEvent->setWorkspace($workspaceMock);

        $this->assertSame($workspaceMock, $workspaceEvent->getWorkspace());
    }
}
