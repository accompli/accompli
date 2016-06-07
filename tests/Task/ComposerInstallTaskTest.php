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
use Accompli\Exception\TaskCommandExecutionException;
use Accompli\Exception\TaskRuntimeException;
use Accompli\Task\ComposerInstallTask;
use PHPUnit_Framework_TestCase;

/**
 * ComposerInstallTaskTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class ComposerInstallTaskTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if ComposerInstallTask::getSubscribedEvents returns an array with at least a AccompliEvents::PREPARE_WORKSPACE and AccompliEvents::INSTALL_RELEASE key.
     */
    public function testGetSubscribedEvents()
    {
        $this->assertInternalType('array', ComposerInstallTask::getSubscribedEvents());
        $this->assertArrayHasKey(AccompliEvents::PREPARE_WORKSPACE, ComposerInstallTask::getSubscribedEvents());
        $this->assertArrayHasKey(AccompliEvents::INSTALL_RELEASE, ComposerInstallTask::getSubscribedEvents());
    }

    /**
     * Tests if constructing a new ComposerInstallTask sets the instance properties.
     */
    public function testOnConstruct()
    {
        $authentication = array('github-oauth' => array('github.com' => 'd6d6c58ee370641927a3e3f76e354dc9a6cf9208'));
        $task = new ComposerInstallTask($authentication);

        $this->assertAttributeSame($authentication, 'authentication', $task);
    }

    /**
     * Tests if ComposerInstallTask::onPrepareWorkspaceInstallComposer calls the connection adapter to install the Composer binary in the workspace.
     */
    public function testOnPrepareWorkspaceInstallComposerInstallsComposer()
    {
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();
        $eventDispatcherMock->expects($this->exactly(5))
                ->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();
        $connectionAdapterMock->expects($this->exactly(2))
                ->method('isFile')
                ->with($this->equalTo('{workspace}/composer.phar'))
                ->willReturnOnConsecutiveCalls(false, true);
        $connectionAdapterMock->expects($this->once())
                ->method('putFile')
                ->with(
                    $this->stringEndsWith('/../Resources/Composer/composer.phar'),
                    $this->equalTo('{workspace}/composer.phar')
                );
        $connectionAdapterMock->expects($this->once())
                ->method('executeCommand')
                ->with('php composer.phar self-update')
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
        $hostMock->expects($this->exactly(5))
                ->method('getPath')
                ->willReturn('{workspace}');

        $workspaceMock = $this->getMockBuilder(Workspace::class)
                ->disableOriginalConstructor()
                ->getMock();

        $event = new WorkspaceEvent($hostMock);
        $event->setWorkspace($workspaceMock);

        $task = new ComposerInstallTask();
        $task->onPrepareWorkspaceInstallComposer($event, AccompliEvents::PREPARE_WORKSPACE, $eventDispatcherMock);
    }

    /**
     * Tests if ComposerInstallTask::onPrepareWorkspaceInstallComposer logs the failure installing the Composer binary.
     */
    public function testOnPrepareWorkspaceInstallComposerFailsInstallingComposer()
    {
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();
        $eventDispatcherMock->expects($this->exactly(1))
                ->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();
        $connectionAdapterMock->expects($this->exactly(2))
                ->method('isFile')
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

        $event = new WorkspaceEvent($hostMock);
        $event->setWorkspace($workspaceMock);

        $this->setExpectedException(TaskRuntimeException::class, 'Failed installing the Composer binary.');

        $task = new ComposerInstallTask();
        $task->onPrepareWorkspaceInstallComposer($event, AccompliEvents::PREPARE_WORKSPACE, $eventDispatcherMock);
    }

    /**
     * Tests if ComposerInstallTask::onPrepareWorkspaceInstallComposer calls the connection adapter to update the Composer binary.
     */
    public function testOnPrepareWorkspaceInstallComposerUpdatesComposer()
    {
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();
        $eventDispatcherMock->expects($this->exactly(3))
                ->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();
        $connectionAdapterMock->expects($this->once())
                ->method('isFile')
                ->willReturn(true);
        $connectionAdapterMock->expects($this->once())
                ->method('executeCommand')
                ->with('php composer.phar self-update')
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

        $workspaceMock = $this->getMockBuilder(Workspace::class)
                ->disableOriginalConstructor()
                ->getMock();

        $event = new WorkspaceEvent($hostMock);
        $event->setWorkspace($workspaceMock);

        $task = new ComposerInstallTask();
        $task->onPrepareWorkspaceInstallComposer($event, AccompliEvents::PREPARE_WORKSPACE, $eventDispatcherMock);
    }

    /**
     * Tests if ComposerInstallTask::onPrepareWorkspaceInstallComposer logs failure of the Composer install.
     */
    public function testOnPrepareWorkspaceInstallComposerFailsUpdatingComposer()
    {
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();
        $eventDispatcherMock->expects($this->exactly(3))
                ->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();
        $connectionAdapterMock->expects($this->once())
                ->method('isFile')
                ->willReturn(true);
        $connectionAdapterMock->expects($this->once())
                ->method('executeCommand')
                ->with('php composer.phar self-update')
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

        $workspaceMock = $this->getMockBuilder(Workspace::class)
                ->disableOriginalConstructor()
                ->getMock();

        $event = new WorkspaceEvent($hostMock);
        $event->setWorkspace($workspaceMock);

        $task = new ComposerInstallTask();
        $task->onPrepareWorkspaceInstallComposer($event, AccompliEvents::PREPARE_WORKSPACE, $eventDispatcherMock);
    }

    /**
     * Tests if ComposerInstallTask::onPrepareWorkspaceInstallComposer throws a RuntimeException when no Workspace instance is available.
     */
    public function testOnPrepareWorkspaceInstallComposerThrowsRuntimeExceptionWhenWorkspaceInstanceNotAvailable()
    {
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();

        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();

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

        $this->setExpectedException(TaskRuntimeException::class, 'The workspace of the host has not been created.');

        $task = new ComposerInstallTask();
        $task->onPrepareWorkspaceInstallComposer($event, AccompliEvents::PREPARE_WORKSPACE, $eventDispatcherMock);
    }

    /**
     * Tests if ComposerInstallTask::onInstallReleaseExecuteComposerInstall calls the connection adapter to execute Composer install.
     */
    public function testOnInstallReleaseExecuteComposerInstall()
    {
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();
        $eventDispatcherMock->expects($this->exactly(3))
                ->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();
        $connectionAdapterMock->expects($this->once())
                ->method('executeCommand')
                ->with('php composer.phar install --no-interaction --working-dir="{workspace}/0.1.0" --no-dev --no-scripts --optimize-autoloader')
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
                ->willReturn('{workspace}/0.1.0');
        $releaseMock->expects($this->once())
                ->method('getWorkspace')
                ->willReturn($workspaceMock);

        $event = new InstallReleaseEvent($releaseMock);

        $task = new ComposerInstallTask();
        $task->onInstallReleaseExecuteComposerInstall($event, AccompliEvents::INSTALL_RELEASE, $eventDispatcherMock);
    }

    /**
     * Tests if ComposerInstallTask::onInstallReleaseExecuteComposerInstall adds the authentication configuration to an auth.json file and deletes it afterwards.
     */
    public function testOnInstallReleaseExecuteComposerInstallWithAuthentication()
    {
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();
        $eventDispatcherMock->expects($this->exactly(3))
                ->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();
        $connectionAdapterMock->expects($this->once())
                ->method('putContents')
                ->with(
                    $this->equalTo('{workspace}/0.1.0/auth.json'),
                    $this->equalTo('{"github-oauth":{"github.com":"d6d6c58ee370641927a3e3f76e354dc9a6cf9208"}}')
                );
        $connectionAdapterMock->expects($this->once())
                ->method('executeCommand')
                ->with('php composer.phar install --no-interaction --working-dir="{workspace}/0.1.0" --no-dev --no-scripts --optimize-autoloader')
                ->willReturn(new ProcessExecutionResult(0, '', ''));
        $connectionAdapterMock->expects($this->once())
                ->method('isFile')
                ->with($this->equalTo('{workspace}/0.1.0/auth.json'))
                ->willReturn(true);
        $connectionAdapterMock->expects($this->once())
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
                ->willReturn('{workspace}/0.1.0');
        $releaseMock->expects($this->once())
                ->method('getWorkspace')
                ->willReturn($workspaceMock);

        $event = new InstallReleaseEvent($releaseMock);

        $task = new ComposerInstallTask(array('github-oauth' => array('github.com' => 'd6d6c58ee370641927a3e3f76e354dc9a6cf9208')));
        $task->onInstallReleaseExecuteComposerInstall($event, AccompliEvents::INSTALL_RELEASE, $eventDispatcherMock);
    }

    /**
     * Tests if ComposerInstallTask::onInstallReleaseExecuteComposerInstall logs failure when Composer install fails.
     */
    public function testOnInstallReleaseExecuteComposerInstallFails()
    {
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();
        $eventDispatcherMock->expects($this->exactly(1))
                ->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();
        $connectionAdapterMock->expects($this->once())
                ->method('executeCommand')
                ->with('php composer.phar install --no-interaction --working-dir="{workspace}/0.1.0" --no-dev --no-scripts --optimize-autoloader')
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
                ->willReturn('{workspace}/0.1.0');
        $releaseMock->expects($this->once())
                ->method('getWorkspace')
                ->willReturn($workspaceMock);

        $event = new InstallReleaseEvent($releaseMock);

        $this->setExpectedException(TaskCommandExecutionException::class, 'Failed installing Composer dependencies.');

        $task = new ComposerInstallTask();
        $task->onInstallReleaseExecuteComposerInstall($event, AccompliEvents::INSTALL_RELEASE, $eventDispatcherMock);
    }
}
