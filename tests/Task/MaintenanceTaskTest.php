<?php

namespace Accompli\Test;

use Accompli\AccompliEvents;
use Accompli\Task\MaintenanceTask;
use Accompli\Utility\VersionCategoryComparator;
use PHPUnit_Framework_TestCase;
use RuntimeException;

/**
 * MaintenanceTaskTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class MaintenanceTaskTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if MaintenanceTask::getSubscribedEvents returns an array with at least a AccompliEvents::PREPARE_WORKSPACE and AccompliEvents::PREPARE_DEPLOY_RELEASE key.
     */
    public function testGetSubscribedEvents()
    {
        $this->assertInternalType('array', MaintenanceTask::getSubscribedEvents());
        $this->assertArrayHasKey(AccompliEvents::PREPARE_WORKSPACE, MaintenanceTask::getSubscribedEvents());
        $this->assertArrayHasKey(AccompliEvents::PREPARE_DEPLOY_RELEASE, MaintenanceTask::getSubscribedEvents());
    }

    /**
     * Tests if constructing a new MaintenanceTask sets the instance properties.
     */
    public function testConstruct()
    {
        $task = new MaintenanceTask();

        $this->assertAttributeSame(VersionCategoryComparator::MATCH_MAJOR_DIFFERENCE, 'strategy', $task);
        $this->assertAttributeSame(realpath(__DIR__.'/../../src/Resources/maintenance'), 'localMaintenanceDirectory', $task);
    }

    /**
     * Tests if constructing a new MaintenanceTask with an invalid strategy throws an InvalidArgumentException.
     *
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage The strategy type "invalid" is invalid.
     */
    public function testConstructThrowsInvalidArgumentException()
    {
        new MaintenanceTask('invalid');
    }

    /**
     * Tests if MaintenanceTask::onPrepareWorkspaceUploadMaintenancePage calls the connection adapter to create and upload the maintenance page in the workspace.
     */
    public function testOnPrepareWorkspaceUploadMaintenancePage()
    {
        $eventDispatcherMock = $this->getMockBuilder('Accompli\EventDispatcher\EventDispatcherInterface')->getMock();
        $eventDispatcherMock->expects($this->atLeast(4))->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder('Accompli\Deployment\Connection\ConnectionAdapterInterface')->getMock();
        $connectionAdapterMock->expects($this->once())->method('isConnected')->willReturn(true);
        $connectionAdapterMock->expects($this->exactly(2))->method('isDirectory')->willReturnOnConsecutiveCalls(false, true);
        $connectionAdapterMock->expects($this->once())->method('createDirectory')->with('/maintenance/')->willReturn(true);
        $connectionAdapterMock->expects($this->atLeastOnce())->method('putFile')->willReturn(true);

        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())->method('hasConnection')->willReturn(true);
        $hostMock->expects($this->once())->method('getConnection')->willReturn($connectionAdapterMock);

        $workspaceMock = $this->getMockBuilder('Accompli\Deployment\Workspace')
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->exactly(1))
                ->method('getHost')
                ->willReturn($hostMock);

        $eventMock = $this->getMockBuilder('Accompli\EventDispatcher\Event\WorkspaceEvent')
                ->disableOriginalConstructor()
                ->getMock();
        $eventMock->expects($this->exactly(1))
                ->method('getWorkspace')
                ->willReturn($workspaceMock);

        $task = new MaintenanceTask();
        $task->onPrepareWorkspaceUploadMaintenancePage($eventMock, AccompliEvents::PREPARE_WORKSPACE, $eventDispatcherMock);
    }

    /**
     * Tests if MaintenanceTask::onPrepareWorkspaceUploadMaintenancePage calls the connection adapter to upload the maintenance page in the workspace.
     */
    public function testOnPrepareWorkspaceUploadMaintenancePageWhenMaintenanceExists()
    {
        $eventDispatcherMock = $this->getMockBuilder('Accompli\EventDispatcher\EventDispatcherInterface')->getMock();
        $eventDispatcherMock->expects($this->atLeast(4))->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder('Accompli\Deployment\Connection\ConnectionAdapterInterface')->getMock();
        $connectionAdapterMock->expects($this->once())->method('isConnected')->willReturn(true);
        $connectionAdapterMock->expects($this->exactly(2))->method('isDirectory')->willReturn(true);
        $connectionAdapterMock->expects($this->never())->method('createDirectory');
        $connectionAdapterMock->expects($this->atLeastOnce())->method('putFile')->willReturn(true);

        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())->method('hasConnection')->willReturn(true);
        $hostMock->expects($this->once())->method('getConnection')->willReturn($connectionAdapterMock);

        $workspaceMock = $this->getMockBuilder('Accompli\Deployment\Workspace')
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->exactly(1))
                ->method('getHost')
                ->willReturn($hostMock);

        $eventMock = $this->getMockBuilder('Accompli\EventDispatcher\Event\WorkspaceEvent')
                ->disableOriginalConstructor()
                ->getMock();
        $eventMock->expects($this->exactly(1))
                ->method('getWorkspace')
                ->willReturn($workspaceMock);

        $task = new MaintenanceTask();
        $task->onPrepareWorkspaceUploadMaintenancePage($eventMock, AccompliEvents::PREPARE_WORKSPACE, $eventDispatcherMock);
    }

    /**
     * Tests if MaintenanceTask::onPrepareWorkspaceUploadMaintenancePage calls the connection adapter when creating the maintenance page directory.
     */
    public function testOnPrepareWorkspaceUploadMaintenancePageFailure()
    {
        $eventDispatcherMock = $this->getMockBuilder('Accompli\EventDispatcher\EventDispatcherInterface')->getMock();
        $eventDispatcherMock->expects($this->exactly(2))->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder('Accompli\Deployment\Connection\ConnectionAdapterInterface')->getMock();
        $connectionAdapterMock->expects($this->once())->method('isConnected')->willReturn(true);
        $connectionAdapterMock->expects($this->exactly(2))->method('isDirectory')->willReturn(false);
        $connectionAdapterMock->expects($this->once())->method('createDirectory')->willReturn(false);
        $connectionAdapterMock->expects($this->never())->method('putFile')->willReturn(true);

        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())->method('hasConnection')->willReturn(true);
        $hostMock->expects($this->once())->method('getConnection')->willReturn($connectionAdapterMock);

        $workspaceMock = $this->getMockBuilder('Accompli\Deployment\Workspace')
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->exactly(1))
                ->method('getHost')
                ->willReturn($hostMock);

        $eventMock = $this->getMockBuilder('Accompli\EventDispatcher\Event\WorkspaceEvent')
                ->disableOriginalConstructor()
                ->getMock();
        $eventMock->expects($this->exactly(1))
                ->method('getWorkspace')
                ->willReturn($workspaceMock);

        $task = new MaintenanceTask();
        $task->onPrepareWorkspaceUploadMaintenancePage($eventMock, AccompliEvents::PREPARE_WORKSPACE, $eventDispatcherMock);
    }

    /**
     * Tests if MaintenanceTask::onPrepareDeployReleaseLinkMaintenancePageToStage calls the connection adapter to link the stage to the maintenance directory.
     */
    public function testOnPrepareDeployReleaseLinkMaintenancePageToStage()
    {
        $eventDispatcherMock = $this->getMockBuilder('Accompli\EventDispatcher\EventDispatcherInterface')->getMock();
        $eventDispatcherMock->expects($this->exactly(2))->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder('Accompli\Deployment\Connection\ConnectionAdapterInterface')->getMock();
        $connectionAdapterMock->expects($this->once())->method('isConnected')->willReturn(true);
        $connectionAdapterMock->expects($this->once())->method('isLink')->willReturn(false);
        $connectionAdapterMock->expects($this->once())->method('link')->with('/maintenance/', '/test')->willReturn(true);
        $connectionAdapterMock->expects($this->never())->method('delete');

        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())->method('hasConnection')->willReturn(true);
        $hostMock->expects($this->once())->method('getConnection')->willReturn($connectionAdapterMock);
        $hostMock->expects($this->once())->method('getStage')->willReturn('test');

        $workspaceMock = $this->getMockBuilder('Accompli\Deployment\Workspace')
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->once())
                ->method('getHost')
                ->willReturn($hostMock);

        $releaseMock = $this->getMockBuilder('Accompli\Deployment\Release')
                ->disableOriginalConstructor()
                ->getMock();

        $eventMock = $this->getMockBuilder('Accompli\EventDispatcher\Event\PrepareDeployReleaseEvent')
                ->disableOriginalConstructor()
                ->getMock();
        $eventMock->expects($this->once())
                ->method('getWorkspace')
                ->willReturn($workspaceMock);
        $eventMock->expects($this->once())
                ->method('getRelease')
                ->willReturn($releaseMock);

        $task = new MaintenanceTask();
        $task->onPrepareDeployReleaseLinkMaintenancePageToStage($eventMock, AccompliEvents::PREPARE_DEPLOY_RELEASE, $eventDispatcherMock);
    }

    /**
     * Tests if MaintenanceTask::onPrepareDeployReleaseLinkMaintenancePageToStage calls the connection adapter to unlink an existing stage link and link the stage to the maintenance directory.
     */
    public function testOnPrepareDeployReleaseLinkMaintenancePageToStageWhenStageLinkExists()
    {
        $eventDispatcherMock = $this->getMockBuilder('Accompli\EventDispatcher\EventDispatcherInterface')->getMock();
        $eventDispatcherMock->expects($this->exactly(2))->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder('Accompli\Deployment\Connection\ConnectionAdapterInterface')->getMock();
        $connectionAdapterMock->expects($this->once())->method('isConnected')->willReturn(true);
        $connectionAdapterMock->expects($this->once())->method('isLink')->willReturn(true);
        $connectionAdapterMock->expects($this->once())->method('link')->with('/maintenance/', '/test')->willReturn(true);
        $connectionAdapterMock->expects($this->once())->method('delete')->with('/test', false)->willReturn(true);

        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())->method('hasConnection')->willReturn(true);
        $hostMock->expects($this->once())->method('getConnection')->willReturn($connectionAdapterMock);
        $hostMock->expects($this->once())->method('getStage')->willReturn('test');

        $workspaceMock = $this->getMockBuilder('Accompli\Deployment\Workspace')
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->once())
                ->method('getHost')
                ->willReturn($hostMock);

        $releaseMock = $this->getMockBuilder('Accompli\Deployment\Release')
                ->disableOriginalConstructor()
                ->getMock();

        $eventMock = $this->getMockBuilder('Accompli\EventDispatcher\Event\PrepareDeployReleaseEvent')
                ->disableOriginalConstructor()
                ->getMock();
        $eventMock->expects($this->once())
                ->method('getWorkspace')
                ->willReturn($workspaceMock);
        $eventMock->expects($this->once())
                ->method('getRelease')
                ->willReturn($releaseMock);

        $task = new MaintenanceTask();
        $task->onPrepareDeployReleaseLinkMaintenancePageToStage($eventMock, AccompliEvents::PREPARE_DEPLOY_RELEASE, $eventDispatcherMock);
    }

    /**
     * Tests if MaintenanceTask::onPrepareDeployReleaseLinkMaintenancePageToStage calls the connection adapter to unlink an existing stage link and link the stage to the maintenance directory.
     */
    public function testOnPrepareDeployReleaseLinkMaintenancePageToStageDoesNotExecuteWhenVersionCategoryDifferenceDoesNotMatchStrategy()
    {
        $eventDispatcherMock = $this->getMockBuilder('Accompli\EventDispatcher\EventDispatcherInterface')->getMock();
        $eventDispatcherMock->expects($this->never())->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder('Accompli\Deployment\Connection\ConnectionAdapterInterface')->getMock();
        $connectionAdapterMock->expects($this->never())->method('isConnected');
        $connectionAdapterMock->expects($this->never())->method('isLink');
        $connectionAdapterMock->expects($this->never())->method('link');
        $connectionAdapterMock->expects($this->never())->method('delete');

        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->never())->method('hasConnection');
        $hostMock->expects($this->never())->method('getConnection');
        $hostMock->expects($this->never())->method('getStage');

        $workspaceMock = $this->getMockBuilder('Accompli\Deployment\Workspace')
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->never())->method('getHost');

        $releaseMock = $this->getMockBuilder('Accompli\Deployment\Release')
                ->disableOriginalConstructor()
                ->getMock();
        $releaseMock->expects($this->exactly(2))
                ->method('getVersion')
                ->willReturn('0.1.0');

        $eventMock = $this->getMockBuilder('Accompli\EventDispatcher\Event\PrepareDeployReleaseEvent')
                ->disableOriginalConstructor()
                ->getMock();
        $eventMock->expects($this->never())->method('getWorkspace');
        $eventMock->expects($this->once())
                ->method('getRelease')
                ->willReturn($releaseMock);
        $eventMock->expects($this->once())
                ->method('getCurrentRelease')
                ->willReturn($releaseMock);

        $task = new MaintenanceTask();
        $task->onPrepareDeployReleaseLinkMaintenancePageToStage($eventMock, AccompliEvents::PREPARE_DEPLOY_RELEASE, $eventDispatcherMock);
    }

    /**
     * Tests if MaintenanceTask::onPrepareDeployReleaseLinkMaintenancePageToStage throws a RuntimeException when the connection adapter fails to link the stage to the maintenance directory.
     *
     * @expectedException        RuntimeException
     * @expectedExceptionMessage Linking "/test" to maintenance page failed.
     */
    public function testOnPrepareDeployReleaseLinkMaintenancePageToStageFailure()
    {
        $eventDispatcherMock = $this->getMockBuilder('Accompli\EventDispatcher\EventDispatcherInterface')->getMock();
        $eventDispatcherMock->expects($this->exactly(3))->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder('Accompli\Deployment\Connection\ConnectionAdapterInterface')->getMock();
        $connectionAdapterMock->expects($this->once())->method('isConnected')->willReturn(true);
        $connectionAdapterMock->expects($this->once())->method('isLink')->willReturn(true);
        $connectionAdapterMock->expects($this->once())->method('delete')->willReturn(false);
        $connectionAdapterMock->expects($this->once())->method('link')->with('/maintenance/', '/test')->willReturn(false);

        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())->method('hasConnection')->willReturn(true);
        $hostMock->expects($this->once())->method('getConnection')->willReturn($connectionAdapterMock);
        $hostMock->expects($this->once())->method('getStage')->willReturn('test');

        $workspaceMock = $this->getMockBuilder('Accompli\Deployment\Workspace')
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->once())
                ->method('getHost')
                ->willReturn($hostMock);

        $releaseMock = $this->getMockBuilder('Accompli\Deployment\Release')
                ->disableOriginalConstructor()
                ->getMock();

        $eventMock = $this->getMockBuilder('Accompli\EventDispatcher\Event\PrepareDeployReleaseEvent')
                ->disableOriginalConstructor()
                ->getMock();
        $eventMock->expects($this->once())
                ->method('getWorkspace')
                ->willReturn($workspaceMock);
        $eventMock->expects($this->once())
                ->method('getRelease')
                ->willReturn($releaseMock);

        $task = new MaintenanceTask();
        $task->onPrepareDeployReleaseLinkMaintenancePageToStage($eventMock, AccompliEvents::PREPARE_DEPLOY_RELEASE, $eventDispatcherMock);
    }
}
