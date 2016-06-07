<?php

namespace Accompli\Test\Task;

use Accompli\AccompliEvents;
use Accompli\Chrono\Process\ProcessExecutionResult;
use Accompli\Deployment\Connection\ConnectionAdapterInterface;
use Accompli\Deployment\Host;
use Accompli\Deployment\Release;
use Accompli\Deployment\Workspace;
use Accompli\EventDispatcher\Event\InstallReleaseEvent;
use Accompli\EventDispatcher\Event\WorkspaceEvent;
use Accompli\EventDispatcher\EventDispatcherInterface;
use Accompli\Task\SSHAgentTask;
use PHPUnit_Framework_TestCase;

/**
 * SSHAgentTaskTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class SSHAgentTaskTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if SSHAgentTask::getSubscribedEvents returns an array with at least a AccompliEvents::PREPARE_WORKSPACE, AccompliEvents::INSTALL_RELEASE_COMPLETE and AccompliEvents::INSTALL_RELEASE_FAILED key.
     */
    public function testGetSubscribedEvents()
    {
        $this->assertInternalType('array', SSHAgentTask::getSubscribedEvents());
        $this->assertArrayHasKey(AccompliEvents::PREPARE_WORKSPACE, SSHAgentTask::getSubscribedEvents());
        $this->assertArrayHasKey(AccompliEvents::INSTALL_RELEASE_COMPLETE, SSHAgentTask::getSubscribedEvents());
        $this->assertArrayHasKey(AccompliEvents::INSTALL_RELEASE_FAILED, SSHAgentTask::getSubscribedEvents());
    }

    /**
     * Tests if constructing a new SSHAgentTask sets the instance properties.
     */
    public function testConstruct()
    {
        $task = new SSHAgentTask(array('SSH-KEY'));

        $this->assertAttributeSame(array('SSH-KEY'), 'keys', $task);
    }

    /**
     * Tests if SSHAgentTask::onPrepareWorkspaceInitializeSSHAgent executes the command to initialize an SSH agent.
     */
    public function testOnPrepareWorkspaceInitializeSSHAgent()
    {
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();
        $eventDispatcherMock->expects($this->exactly(2))
                ->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();
        $connectionAdapterMock->expects($this->once())
                ->method('executeCommand')
                ->with($this->equalTo('eval $(ssh-agent)'))
                ->willReturn(new ProcessExecutionResult(0, '', ''));

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

        $task = new SSHAgentTask();
        $task->onPrepareWorkspaceInitializeSSHAgent($event, AccompliEvents::PREPARE_WORKSPACE, $eventDispatcherMock);
    }

    /**
     * Tests if SSHAgentTask::onPrepareWorkspaceInitializeSSHAgent executing the command to initialize an SSH agent fails.
     *
     * @depends testOnPrepareWorkspaceInitializeSSHAgent
     */
    public function testOnPrepareWorkspaceInitializeSSHAgentFails()
    {
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();
        $eventDispatcherMock->expects($this->exactly(2))
                ->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();
        $connectionAdapterMock->expects($this->once())
                ->method('executeCommand')
                ->with($this->equalTo('eval $(ssh-agent)'))
                ->willReturn(new ProcessExecutionResult(1, '', ''));

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

        $task = new SSHAgentTask();
        $task->onPrepareWorkspaceInitializeSSHAgent($event, AccompliEvents::PREPARE_WORKSPACE, $eventDispatcherMock);
    }

    /**
     * Tests if SSHAgentTask::onPrepareWorkspaceInitializeSSHAgent executes the command to add a SSH key to the SSH agent.
     *
     * @depends testOnPrepareWorkspaceInitializeSSHAgent
     */
    public function testOnPrepareWorkspaceInitializeSSHAgentAddSSHKey()
    {
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();
        $eventDispatcherMock->expects($this->exactly(4))
                ->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();
        $connectionAdapterMock->expects($this->exactly(2))
                ->method('executeCommand')
                ->withConsecutive(
                    array($this->equalTo('eval $(ssh-agent)')),
                    array($this->equalTo('ssh-add'), $this->equalTo(array('{workspace}/tmp.key')))
                )
                ->willReturnOnConsecutiveCalls(new ProcessExecutionResult(0, '', ''), new ProcessExecutionResult(0, '', ''));
        $connectionAdapterMock->expects($this->once())
                ->method('createFile')
                ->with($this->equalTo('{workspace}/tmp.key'), $this->equalTo(0700))
                ->willReturn(true);
        $connectionAdapterMock->expects($this->once())
                ->method('putContents')
                ->with($this->equalTo('{workspace}/tmp.key'), $this->equalTo('SSH-KEY'))
                ->willReturn(true);
        $connectionAdapterMock->expects($this->once())
                ->method('delete')
                ->with($this->equalTo('{workspace}/tmp.key'))
                ->willReturn(true);

        $hostMock = $this->getMockBuilder(Host::class)
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())
                ->method('hasConnection')
                ->willReturn(true);
        $hostMock->expects($this->once())
                ->method('getConnection')
                ->willReturn($connectionAdapterMock);
        $hostMock->expects($this->once())
                ->method('getPath')
                ->willReturn('{workspace}');

        $workspaceMock = $this->getMockBuilder(Workspace::class)
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->once())
                ->method('getHost')
                ->willReturn($hostMock);

        $event = new WorkspaceEvent($hostMock);
        $event->setWorkspace($workspaceMock);

        $task = new SSHAgentTask(array('SSH-KEY'));
        $task->onPrepareWorkspaceInitializeSSHAgent($event, AccompliEvents::PREPARE_WORKSPACE, $eventDispatcherMock);
    }

    /**
     * Tests if SSHAgentTask::onPrepareWorkspaceInitializeSSHAgent executing the command to add a SSH key to the SSH agent fails.
     *
     * @depends testOnPrepareWorkspaceInitializeSSHAgentAddSSHKey
     */
    public function testOnPrepareWorkspaceInitializeSSHAgentAddSSHKeyFails()
    {
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();
        $eventDispatcherMock->expects($this->exactly(4))
                ->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();
        $connectionAdapterMock->expects($this->exactly(2))
                ->method('executeCommand')
                ->withConsecutive(
                    array($this->equalTo('eval $(ssh-agent)')),
                    array($this->equalTo('ssh-add'), $this->equalTo(array('{workspace}/tmp.key')))
                )
                ->willReturnOnConsecutiveCalls(new ProcessExecutionResult(0, '', ''), new ProcessExecutionResult(1, '', ''));
        $connectionAdapterMock->expects($this->once())
                ->method('createFile')
                ->with($this->equalTo('{workspace}/tmp.key'), $this->equalTo(0700))
                ->willReturn(true);
        $connectionAdapterMock->expects($this->once())
                ->method('putContents')
                ->with($this->equalTo('{workspace}/tmp.key'), $this->equalTo('SSH-KEY'))
                ->willReturn(true);
        $connectionAdapterMock->expects($this->once())
                ->method('delete')
                ->with($this->equalTo('{workspace}/tmp.key'))
                ->willReturn(true);

        $hostMock = $this->getMockBuilder(Host::class)
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())
                ->method('hasConnection')
                ->willReturn(true);
        $hostMock->expects($this->once())
                ->method('getConnection')
                ->willReturn($connectionAdapterMock);
        $hostMock->expects($this->once())
                ->method('getPath')
                ->willReturn('{workspace}');

        $workspaceMock = $this->getMockBuilder(Workspace::class)
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->once())
                ->method('getHost')
                ->willReturn($hostMock);

        $event = new WorkspaceEvent($hostMock);
        $event->setWorkspace($workspaceMock);

        $task = new SSHAgentTask(array('SSH-KEY'));
        $task->onPrepareWorkspaceInitializeSSHAgent($event, AccompliEvents::PREPARE_WORKSPACE, $eventDispatcherMock);
    }

    /**
     * Tests if SSHAgentTask::onInstallReleaseCompleteOrFailedShutdownSSHAgent executes the command to shutdown the SSH agent.
     *
     * @depends testOnPrepareWorkspaceInitializeSSHAgent
     */
    public function testOnInstallReleaseCompleteOrFailedShutdownSSHAgent()
    {
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();
        $eventDispatcherMock->expects($this->exactly(4))->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();
        $connectionAdapterMock->expects($this->exactly(2))
                ->method('executeCommand')
                ->withConsecutive(
                    array($this->equalTo('eval $(ssh-agent)')),
                    array($this->equalTo('eval $(ssh-agent -k)'))
                )
                ->willReturn(new ProcessExecutionResult(0, '', ''));

        $hostMock = $this->getMockBuilder(Host::class)
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->exactly(2))
                ->method('hasConnection')
                ->willReturn(true);
        $hostMock->expects($this->exactly(2))
                ->method('getConnection')
                ->willReturn($connectionAdapterMock);

        $event = new WorkspaceEvent($hostMock);

        $task = new SSHAgentTask();
        $task->onPrepareWorkspaceInitializeSSHAgent($event, AccompliEvents::PREPARE_WORKSPACE, $eventDispatcherMock);

        $releaseMock = $this->getMockBuilder(Release::class)
                ->disableOriginalConstructor()
                ->getMock();

        $event = new InstallReleaseEvent($releaseMock);

        $task->onInstallReleaseCompleteOrFailedShutdownSSHAgent($event, AccompliEvents::INSTALL_RELEASE_COMPLETE, $eventDispatcherMock);
    }

    /**
     * Tests if SSHAgentTask::onInstallReleaseCompleteOrFailedShutdownSSHAgent executing the command to shutdown the SSH agent fails.
     *
     * @depends testOnInstallReleaseCompleteOrFailedShutdownSSHAgent
     */
    public function testOnInstallReleaseCompleteOrFailedShutdownSSHAgentFails()
    {
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();
        $eventDispatcherMock->expects($this->exactly(4))
                ->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();
        $connectionAdapterMock->expects($this->exactly(2))
                ->method('executeCommand')
                ->withConsecutive(
                    array($this->equalTo('eval $(ssh-agent)')),
                    array($this->equalTo('eval $(ssh-agent -k)'))
                )
                ->willReturnOnConsecutiveCalls(new ProcessExecutionResult(0, '', ''), new ProcessExecutionResult(1, '', ''));

        $hostMock = $this->getMockBuilder(Host::class)
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->exactly(2))
                ->method('hasConnection')
                ->willReturn(true);
        $hostMock->expects($this->exactly(2))
                ->method('getConnection')
                ->willReturn($connectionAdapterMock);

        $event = new WorkspaceEvent($hostMock);

        $task = new SSHAgentTask();
        $task->onPrepareWorkspaceInitializeSSHAgent($event, AccompliEvents::PREPARE_WORKSPACE, $eventDispatcherMock);

        $releaseMock = $this->getMockBuilder(Release::class)
                ->disableOriginalConstructor()
                ->getMock();

        $event = new InstallReleaseEvent($releaseMock);

        $task->onInstallReleaseCompleteOrFailedShutdownSSHAgent($event, AccompliEvents::INSTALL_RELEASE_COMPLETE, $eventDispatcherMock);
    }
}
