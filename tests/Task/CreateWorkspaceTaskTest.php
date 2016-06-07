<?php

namespace Accompli\Test\Task;

use Accompli\AccompliEvents;
use Accompli\Deployment\Connection\ConnectionAdapterInterface;
use Accompli\Deployment\Host;
use Accompli\Deployment\Workspace;
use Accompli\EventDispatcher\Event\WorkspaceEvent;
use Accompli\EventDispatcher\EventDispatcherInterface;
use Accompli\Task\CreateWorkspaceTask;
use PHPUnit_Framework_TestCase;
use RuntimeException;

/**
 * CreateWorkspaceTaskTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class CreateWorkspaceTaskTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if CreateWorkspaceTask::getSubscribedEvents returns an array with at least a AccompliEvents::PREPARE_WORKSPACE and AccompliEvents::GET_WORKSPACE key.
     */
    public function testGetSubscribedEvents()
    {
        $this->assertInternalType('array', CreateWorkspaceTask::getSubscribedEvents());
        $this->assertArrayHasKey(AccompliEvents::PREPARE_WORKSPACE, CreateWorkspaceTask::getSubscribedEvents());
        $this->assertArrayHasKey(AccompliEvents::GET_WORKSPACE, CreateWorkspaceTask::getSubscribedEvents());
    }

    /**
     * Tests if constructing a new CreateWorkspaceTask sets the instance properties with defaults.
     */
    public function testConstruct()
    {
        $task = new CreateWorkspaceTask();

        $this->assertAttributeSame('releases/', 'releasesDirectory', $task);
        $this->assertAttributeSame('data/', 'dataDirectory', $task);
        $this->assertAttributeSame('cache/', 'cacheDirectory', $task);
    }

    /**
     * Tests if constructing a new CreateWorkspaceTask sets the instance properties with values from the arguments.
     *
     * @depends testConstruct
     */
    public function testConstructWithArguments()
    {
        $task = new CreateWorkspaceTask('test-releases/', 'test-data/', 'test-cache/');

        $this->assertAttributeSame('test-releases/', 'releasesDirectory', $task);
        $this->assertAttributeSame('test-data/', 'dataDirectory', $task);
        $this->assertAttributeSame('test-cache/', 'cacheDirectory', $task);
    }

    /**
     * Tests if CreateWorkspaceTask::onPrepareWorkspaceConstructWorkspaceInstance constructs a new Workspace instance and sets it on the WorkspaceEvent.
     *
     * @depends testConstruct
     */
    public function testOnPrepareWorkspaceConstructWorkspaceInstance()
    {
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();
        $eventDispatcherMock->expects($this->exactly(2))
                ->method('dispatch');

        $hostMock = $this->getMockBuilder(Host::class)
                ->disableOriginalConstructor()
                ->getMock();

        $event = new WorkspaceEvent($hostMock);

        $task = new CreateWorkspaceTask();
        $task->onPrepareWorkspaceConstructWorkspaceInstance($event, AccompliEvents::PREPARE_WORKSPACE, $eventDispatcherMock);

        $this->assertInstanceOf(Workspace::class, $event->getWorkspace());
        $this->assertSame('/releases/', $event->getWorkspace()->getReleasesDirectory());
        $this->assertSame('/data/', $event->getWorkspace()->getDataDirectory());
        $this->assertSame('/cache/', $event->getWorkspace()->getCacheDirectory());
    }

    /**
     * Tests if CreateWorkspaceTask::onPrepareWorkspaceCreateWorkspace throws a RuntimeException when the workspace path does not exist and cannot be created.
     *
     * @depends testOnPrepareWorkspaceConstructWorkspaceInstance
     */
    public function testOnPrepareWorkspaceCreateWorkspaceThrowsRuntimeExceptionOnInaccessibleWorkspacePath()
    {
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();
        $eventDispatcherMock->expects($this->exactly(2))
                ->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();
        $connectionAdapterMock->expects($this->once())
                ->method('isConnected')
                ->willReturn(true);
        $connectionAdapterMock->expects($this->once())
                ->method('isDirectory')
                ->willReturn(false);
        $connectionAdapterMock->expects($this->once())
                ->method('createDirectory')
                ->with('')
                ->willReturn(false);

        $hostMock = $this->getMockBuilder(Host::class)
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())
                ->method('hasConnection')
                ->willReturn(true);
        $hostMock->expects($this->once())
                ->method('getConnection')
                ->willReturn($connectionAdapterMock);

        $event = new WorkspaceEvent($hostMock);

        $task = new CreateWorkspaceTask();
        $task->onPrepareWorkspaceConstructWorkspaceInstance($event, AccompliEvents::PREPARE_WORKSPACE, $eventDispatcherMock);

        $this->setExpectedException(RuntimeException::class, 'The workspace path "" does not exist and could not be created.');

        $task->onPrepareWorkspaceCreateWorkspace($event, AccompliEvents::PREPARE_WORKSPACE, $eventDispatcherMock);
    }

    /**
     * Tests if CreateWorkspaceTask::onPrepareWorkspaceCreateWorkspace calls the connection adapter to create the workspace directories.
     *
     * @depends testOnPrepareWorkspaceCreateWorkspaceThrowsRuntimeExceptionOnInaccessibleWorkspacePath
     */
    public function testOnPrepareWorkspaceCreateWorkspace()
    {
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();
        $eventDispatcherMock->expects($this->exactly(8))
                ->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();
        $connectionAdapterMock->expects($this->once())
                ->method('isConnected')
                ->willReturn(true);
        $connectionAdapterMock->expects($this->exactly(4))
                ->method('isDirectory')
                ->willReturnOnConsecutiveCalls(true, false, false, false);
        $connectionAdapterMock->expects($this->exactly(3))
                ->method('createDirectory')
                ->withConsecutive(array('/releases/'), array('/data/'), array('/cache/'))
                ->willReturnOnConsecutiveCalls(true, true, true);

        $hostMock = $this->getMockBuilder(Host::class)
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())
                ->method('hasConnection')
                ->willReturn(true);
        $hostMock->expects($this->once())
                ->method('getConnection')
                ->willReturn($connectionAdapterMock);

        $event = new WorkspaceEvent($hostMock);

        $task = new CreateWorkspaceTask();
        $task->onPrepareWorkspaceConstructWorkspaceInstance($event, AccompliEvents::PREPARE_WORKSPACE, $eventDispatcherMock);
        $task->onPrepareWorkspaceCreateWorkspace($event, AccompliEvents::PREPARE_WORKSPACE, $eventDispatcherMock);
    }

    /**
     * Tests if CreateWorkspaceTask::onPrepareWorkspaceCreateWorkspace calls the connection adapter to create the workspace directories.
     *
     * @depends testOnPrepareWorkspaceCreateWorkspaceThrowsRuntimeExceptionOnInaccessibleWorkspacePath
     */
    public function testOnPrepareWorkspaceCreateWorkspaceFailure()
    {
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();
        $eventDispatcherMock->expects($this->exactly(8))
                ->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();
        $connectionAdapterMock->expects($this->once())
                ->method('isConnected')
                ->willReturn(true);
        $connectionAdapterMock->expects($this->exactly(4))
                ->method('isDirectory')
                ->willReturnOnConsecutiveCalls(true, false, false, false);
        $connectionAdapterMock->expects($this->exactly(3))
                ->method('createDirectory')
                ->withConsecutive(array('/releases/'), array('/data/'), array('/cache/'))
                ->willReturn(false);

        $hostMock = $this->getMockBuilder(Host::class)
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())
                ->method('hasConnection')
                ->willReturn(true);
        $hostMock->expects($this->once())
                ->method('getConnection')
                ->willReturn($connectionAdapterMock);

        $event = new WorkspaceEvent($hostMock);

        $task = new CreateWorkspaceTask();
        $task->onPrepareWorkspaceConstructWorkspaceInstance($event, AccompliEvents::PREPARE_WORKSPACE, $eventDispatcherMock);
        $task->onPrepareWorkspaceCreateWorkspace($event, AccompliEvents::PREPARE_WORKSPACE, $eventDispatcherMock);
    }

    /**
     * Tests if CreateWorkspaceTask::onPrepareWorkspaceCreateWorkspace calls the connection adapter to create the workspace directories.
     *
     * @depends testOnPrepareWorkspaceCreateWorkspaceThrowsRuntimeExceptionOnInaccessibleWorkspacePath
     */
    public function testOnPrepareWorkspaceCreateWorkspaceExists()
    {
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();
        $eventDispatcherMock->expects($this->exactly(8))
                ->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();
        $connectionAdapterMock->expects($this->once())
                ->method('isConnected')
                ->willReturn(true);
        $connectionAdapterMock->expects($this->exactly(4))
                ->method('isDirectory')
                ->willReturn(true);
        $connectionAdapterMock->expects($this->never())
                ->method('createDirectory');

        $hostMock = $this->getMockBuilder(Host::class)
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())
                ->method('hasConnection')
                ->willReturn(true);
        $hostMock->expects($this->once())
                ->method('getConnection')
                ->willReturn($connectionAdapterMock);

        $event = new WorkspaceEvent($hostMock);

        $task = new CreateWorkspaceTask();
        $task->onPrepareWorkspaceConstructWorkspaceInstance($event, AccompliEvents::PREPARE_WORKSPACE, $eventDispatcherMock);
        $task->onPrepareWorkspaceCreateWorkspace($event, AccompliEvents::PREPARE_WORKSPACE, $eventDispatcherMock);
    }
}
