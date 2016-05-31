<?php

namespace Accompli\Test\Task;

use Accompli\AccompliEvents;
use Accompli\Deployment\Connection\ConnectionAdapterInterface;
use Accompli\Deployment\Host;
use Accompli\Deployment\Release;
use Accompli\Deployment\Workspace;
use Accompli\EventDispatcher\Event\DeployReleaseEvent;
use Accompli\EventDispatcher\Event\PrepareDeployReleaseEvent;
use Accompli\EventDispatcher\EventDispatcherInterface;
use Accompli\Task\DeployReleaseTask;
use PHPUnit_Framework_TestCase;
use RuntimeException;

/**
 * DeployReleaseTaskTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class DeployReleaseTaskTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if DeployReleaseTask::getSubscribedEvents returns an array with at least a AccompliEvents::PREPARE_DEPLOY_RELEASE, AccompliEvents::DEPLOY_RELEASE and AccompliEvents::ROLLBACK_RELEASE key.
     */
    public function testGetSubscribedEvents()
    {
        $this->assertInternalType('array', DeployReleaseTask::getSubscribedEvents());
        $this->assertArrayHasKey(AccompliEvents::PREPARE_DEPLOY_RELEASE, DeployReleaseTask::getSubscribedEvents());
        $this->assertArrayHasKey(AccompliEvents::DEPLOY_RELEASE, DeployReleaseTask::getSubscribedEvents());
        $this->assertArrayHasKey(AccompliEvents::ROLLBACK_RELEASE, DeployReleaseTask::getSubscribedEvents());
    }

    /**
     * Tests if DeployReleaseTask::onPrepareDeployReleaseConstructReleaseInstances creates a release instance and sets it into the PrepareDeployReleaseEvent.
     */
    public function testOnPrepareDeployReleaseConstructReleaseInstances()
    {
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();

        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();
        $connectionAdapterMock->expects($this->once())
                ->method('isConnected')
                ->willReturn(true);
        $connectionAdapterMock->expects($this->once())
                ->method('isDirectory')
                ->with($this->equalTo('/path/to/workspace/releases/0.1.0'))
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

        $workspaceMock = $this->getMockBuilder(Workspace::class)
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->once())
                ->method('getHost')
                ->willReturn($hostMock);
        $workspaceMock->expects($this->once())
                ->method('getReleasesDirectory')
                ->willReturn('/path/to/workspace/releases');
        $workspaceMock->expects($this->once())
                ->method('addRelease')
                ->with($this->callback(function ($release) use ($workspaceMock) {
                    if ($release instanceof Release) {
                        $release->setWorkspace($workspaceMock);
                    }

                    return ($release instanceof Release);
                }));

        $eventMock = $this->getMockBuilder(PrepareDeployReleaseEvent::class)
                ->disableOriginalConstructor()
                ->getMock();
        $eventMock->expects($this->once())
                ->method('getWorkspace')
                ->willReturn($workspaceMock);
        $eventMock->expects($this->once())
                ->method('getVersion')
                ->willReturn('0.1.0');
        $eventMock->expects($this->once())
                ->method('setRelease')
                ->with($this->callback(function ($release) {
                    return ($release instanceof Release && $release->getVersion() === '0.1.0');
                }));

        $task = new DeployReleaseTask();
        $task->onPrepareDeployReleaseConstructReleaseInstances($eventMock, AccompliEvents::PREPARE_DEPLOY_RELEASE, $eventDispatcherMock);
    }

    /**
     * Tests if DeployReleaseTask::onPrepareDeployReleaseConstructReleaseInstances creates a release instance for the release being deployed and the release instance of the current release and sets them into the PrepareDeployReleaseEvent.
     *
     * @depends testOnPrepareDeployReleaseConstructReleaseInstances
     */
    public function testOnPrepareDeployReleaseConstructReleaseInstancesWithCurrentRelease()
    {
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();
        $eventDispatcherMock->expects($this->once())
                ->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();
        $connectionAdapterMock->expects($this->once())
                ->method('isConnected')
                ->willReturn(true);
        $connectionAdapterMock->expects($this->once())
                ->method('isDirectory')
                ->with($this->equalTo('/path/to/workspace/releases//0.1.0'))
                ->willReturn(true);
        $connectionAdapterMock->expects($this->once())
                ->method('isLink')
                ->with($this->equalTo('/path/to/workspace/test'))
                ->willReturn(true);
        $connectionAdapterMock->expects($this->once())
                ->method('readLink')
                ->with($this->equalTo('/path/to/workspace/test'))
                ->willReturn('/path/to/workspace/releases/0.1.0');

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
                ->willReturn('/path/to/workspace');
        $hostMock->expects($this->once())
                ->method('getStage')
                ->willReturn(Host::STAGE_TEST);

        $workspaceMock = $this->getMockBuilder(Workspace::class)
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->once())
                ->method('getHost')
                ->willReturn($hostMock);
        $workspaceMock->expects($this->exactly(3))
                ->method('getReleasesDirectory')
                ->willReturn('/path/to/workspace/releases/');
        $workspaceMock->expects($this->exactly(2))
                ->method('addRelease')
                ->with($this->callback(function ($release) use ($workspaceMock) {
                    if ($release instanceof Release) {
                        $release->setWorkspace($workspaceMock);
                    }

                    return ($release instanceof Release && $release->getVersion() === '0.1.0');
                }));

        $eventMock = $this->getMockBuilder(PrepareDeployReleaseEvent::class)
                ->disableOriginalConstructor()
                ->getMock();
        $eventMock->expects($this->once())
                ->method('getWorkspace')
                ->willReturn($workspaceMock);
        $eventMock->expects($this->once())
                ->method('getVersion')
                ->willReturn('0.1.0');
        $eventMock->expects($this->once())
                ->method('setRelease')
                ->with($this->callback(function ($release) {
                    return ($release instanceof Release && $release->getVersion() === '0.1.0');
                }));
        $eventMock->expects($this->once())
                ->method('setCurrentRelease')
                ->with($this->callback(function ($release) {
                    return ($release instanceof Release && $release->getVersion() === '0.1.0');
                }));

        $task = new DeployReleaseTask();
        $task->onPrepareDeployReleaseConstructReleaseInstances($eventMock, AccompliEvents::PREPARE_DEPLOY_RELEASE, $eventDispatcherMock);
    }

    /**
     * Tests if DeployReleaseTask::onPrepareDeployReleaseConstructReleaseInstances throws a RuntimeException when the directory of the release currently being deployed isn't found within the workspace.
     *
     * @depends testOnPrepareDeployReleaseConstructReleaseInstances
     */
    public function testOnPrepareDeployReleaseConstructReleaseInstancesThrowsRuntimeExceptionWhenPathToReleaseDoesNotExist()
    {
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();

        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();
        $connectionAdapterMock->expects($this->once())
                ->method('isConnected')
                ->willReturn(true);
        $connectionAdapterMock->expects($this->once())
                ->method('isDirectory')
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

        $workspaceMock = $this->getMockBuilder(Workspace::class)
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->once())
                ->method('getHost')
                ->willReturn($hostMock);
        $workspaceMock->expects($this->once())
                ->method('addRelease')
                ->with($this->callback(function ($release) use ($workspaceMock) {
                    if ($release instanceof Release) {
                        $release->setWorkspace($workspaceMock);
                    }

                    return ($release instanceof Release);
                }));

        $eventMock = $this->getMockBuilder(PrepareDeployReleaseEvent::class)
                ->disableOriginalConstructor()
                ->getMock();
        $eventMock->expects($this->once())
                ->method('getWorkspace')
                ->willReturn($workspaceMock);
        $eventMock->expects($this->once())
                ->method('getVersion')
                ->willReturn('0.1.0');
        $eventMock->expects($this->never())
                ->method('setRelease');

        $task = new DeployReleaseTask();

        $this->setExpectedException(RuntimeException::class, 'The release "0.1.0" is not installed within the workspace.');

        $task->onPrepareDeployReleaseConstructReleaseInstances($eventMock, AccompliEvents::PREPARE_DEPLOY_RELEASE, $eventDispatcherMock);
    }

    /**
     * Tests if DeployReleaseTask::onDeployReleaseLinkRelease links the release directory to the stage.
     */
    public function testOnDeployReleaseLinkReleaseWithoutCurrentRelease()
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
        $connectionAdapterMock->expects($this->exactly(2))
                ->method('isLink')
                ->with($this->equalTo('/path/to/workspace/test'))
                ->willReturn(false);
        $connectionAdapterMock->expects($this->once())
                ->method('link')
                ->with($this->equalTo('/path/to/workspace/releases/0.1.0'), $this->equalTo('/path/to/workspace/test'))
                ->willReturn(true);
        $connectionAdapterMock->expects($this->never())
                ->method('delete');

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
                ->willReturn('/path/to/workspace');
        $hostMock->expects($this->once())
                ->method('getStage')
                ->willReturn(Host::STAGE_TEST);

        $workspaceMock = $this->getMockBuilder(Workspace::class)
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->once())
                ->method('getHost')
                ->willReturn($hostMock);

        $releaseMock = $this->getMockBuilder(Release::class)
                ->disableOriginalConstructor()
                ->getMock();
        $releaseMock->expects($this->once())
                ->method('getPath')
                ->willReturn('/path/to/workspace/releases/0.1.0');
        $releaseMock->expects($this->once())
                ->method('getWorkspace')
                ->willReturn($workspaceMock);

        $eventMock = $this->getMockBuilder(DeployReleaseEvent::class)
                ->disableOriginalConstructor()
                ->getMock();
        $eventMock->expects($this->once())
                ->method('getRelease')
                ->willReturn($releaseMock);

        $task = new DeployReleaseTask();
        $task->onDeployOrRollbackReleaseLinkRelease($eventMock, AccompliEvents::DEPLOY_RELEASE, $eventDispatcherMock);
    }

    /**
     * Tests if DeployReleaseTask::onDeployReleaseLinkRelease links the release directory to the stage when a release is already linked.
     *
     * @depends testOnDeployReleaseLinkReleaseWithoutCurrentRelease
     */
    public function testOnDeployReleaseLinkReleaseWithCurrentRelease()
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
        $connectionAdapterMock->expects($this->exactly(2))
                ->method('isLink')
                ->with($this->equalTo('/path/to/workspace/test'))
                ->willReturn(true);
        $connectionAdapterMock->expects($this->once())
                ->method('link')
                ->with($this->equalTo('/path/to/workspace/releases/0.1.0'), $this->equalTo('/path/to/workspace/test'))
                ->willReturn(true);
        $connectionAdapterMock->expects($this->once())
                ->method('readLink')
                ->with($this->equalTo('/path/to/workspace/test'))
                ->willReturn('/path/to/workspace/releases/master');
        $connectionAdapterMock->expects($this->once())
                ->method('delete')
                ->with($this->equalTo('/path/to/workspace/test'), $this->equalTo(false));

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
                ->willReturn('/path/to/workspace');
        $hostMock->expects($this->once())
                ->method('getStage')
                ->willReturn(Host::STAGE_TEST);

        $workspaceMock = $this->getMockBuilder(Workspace::class)
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->once())
                ->method('getHost')
                ->willReturn($hostMock);

        $releaseMock = $this->getMockBuilder(Release::class)
                ->disableOriginalConstructor()
                ->getMock();
        $releaseMock->expects($this->exactly(2))
                ->method('getPath')
                ->willReturn('/path/to/workspace/releases/0.1.0');
        $releaseMock->expects($this->once())
                ->method('getWorkspace')
                ->willReturn($workspaceMock);

        $eventMock = $this->getMockBuilder(DeployReleaseEvent::class)
                ->disableOriginalConstructor()
                ->getMock();
        $eventMock->expects($this->once())
                ->method('getRelease')
                ->willReturn($releaseMock);

        $task = new DeployReleaseTask();
        $task->onDeployOrRollbackReleaseLinkRelease($eventMock, AccompliEvents::DEPLOY_RELEASE, $eventDispatcherMock);
    }

    /**
     * Tests if DeployReleaseTask::onDeployReleaseLinkRelease does not (un)link the release directory to the stage when the same release is already deployed.
     *
     * @depends testOnDeployReleaseLinkReleaseWithCurrentRelease
     */
    public function testOnDeployReleaseLinkReleaseWithCurrentReleaseIsSameAsReleaseBeingDeployed()
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
                ->method('isLink')
                ->with($this->equalTo('/path/to/workspace/test'))
                ->willReturn(true);
        $connectionAdapterMock->expects($this->never())->method('link');
        $connectionAdapterMock->expects($this->once())
                ->method('readLink')
                ->with($this->equalTo('/path/to/workspace/test'))
                ->willReturn('/path/to/workspace/releases/0.1.0');
        $connectionAdapterMock->expects($this->never())->method('delete');

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
                ->willReturn('/path/to/workspace');
        $hostMock->expects($this->once())
                ->method('getStage')
                ->willReturn(Host::STAGE_TEST);

        $workspaceMock = $this->getMockBuilder(Workspace::class)
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->once())
                ->method('getHost')
                ->willReturn($hostMock);

        $releaseMock = $this->getMockBuilder(Release::class)
                ->disableOriginalConstructor()
                ->getMock();
        $releaseMock->expects($this->once())
                ->method('getPath')
                ->willReturn('/path/to/workspace/releases/0.1.0');
        $releaseMock->expects($this->once())
                ->method('getWorkspace')
                ->willReturn($workspaceMock);

        $eventMock = $this->getMockBuilder(DeployReleaseEvent::class)
                ->disableOriginalConstructor()
                ->getMock();
        $eventMock->expects($this->once())
                ->method('getRelease')
                ->willReturn($releaseMock);

        $task = new DeployReleaseTask();
        $task->onDeployOrRollbackReleaseLinkRelease($eventMock, AccompliEvents::DEPLOY_RELEASE, $eventDispatcherMock);
    }

    /**
     * Tests if DeployReleaseTask::onDeployReleaseLinkRelease throws a RuntimeException when linking to a release fails.
     *
     * @depends testOnDeployReleaseLinkReleaseWithCurrentReleaseIsSameAsReleaseBeingDeployed
     */
    public function testOnDeployReleaseLinkReleaseThrowsRuntimeExceptionWhenLinkingFails()
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
        $connectionAdapterMock->expects($this->exactly(2))
                ->method('isLink')
                ->with($this->equalTo('/path/to/workspace/test'))
                ->willReturn(false);
        $connectionAdapterMock->expects($this->once())
                ->method('link')
                ->willReturn(false);
        $connectionAdapterMock->expects($this->never())
                ->method('delete');

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
                ->willReturn('/path/to/workspace');
        $hostMock->expects($this->once())
                ->method('getStage')
                ->willReturn(Host::STAGE_TEST);

        $workspaceMock = $this->getMockBuilder(Workspace::class)
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->once())
                ->method('getHost')
                ->willReturn($hostMock);

        $releaseMock = $this->getMockBuilder(Release::class)
                ->disableOriginalConstructor()
                ->getMock();
        $releaseMock->expects($this->once())
                ->method('getPath')
                ->willReturn('/path/to/workspace/releases/0.1.0');
        $releaseMock->expects($this->once())
                ->method('getVersion')
                ->willReturn('0.1.0');
        $releaseMock->expects($this->once())
                ->method('getWorkspace')
                ->willReturn($workspaceMock);

        $eventMock = $this->getMockBuilder(DeployReleaseEvent::class)
                ->disableOriginalConstructor()
                ->getMock();
        $eventMock->expects($this->once())
                ->method('getRelease')
                ->willReturn($releaseMock);

        $task = new DeployReleaseTask();

        $this->setExpectedException(RuntimeException::class, 'Linking "/path/to/workspace/test" to release "0.1.0" failed.');

        $task->onDeployOrRollbackReleaseLinkRelease($eventMock, AccompliEvents::DEPLOY_RELEASE, $eventDispatcherMock);
    }
}
