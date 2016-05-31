<?php

namespace Accompli\Test\EventDispatcher\Event;

use Accompli\Deployment\Host;
use Accompli\Deployment\Workspace;
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
        $hostMock = $this->getMockBuilder(Host::class)
                ->disableOriginalConstructor()
                ->getMock();

        $workspaceMock = $this->getMockBuilder(Workspace::class)
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
        $hostMock = $this->getMockBuilder(Host::class)
                ->disableOriginalConstructor()
                ->getMock();

        $workspaceMock = $this->getMockBuilder(Workspace::class)
                ->disableOriginalConstructor()
                ->getMock();

        $workspaceEvent = new WorkspaceEvent($hostMock);
        $workspaceEvent->setWorkspace($workspaceMock);

        $this->assertSame($workspaceMock, $workspaceEvent->getWorkspace());
    }
}
