<?php

namespace Accompli\Test\Task;

use Accompli\AccompliEvents;
use Accompli\Deployment\Connection\ConnectionAdapterInterface;
use Accompli\Deployment\Host;
use Accompli\Deployment\Release;
use Accompli\Deployment\Workspace;
use Accompli\EventDispatcher\Event\InstallReleaseEvent;
use Accompli\EventDispatcher\EventDispatcherInterface;
use Accompli\Exception\TaskRuntimeException;
use Accompli\Task\FilePermissionTask;
use PHPUnit_Framework_TestCase;

/**
 * FilePermissionTaskTest.
 *
 * @author Deborah van der Vegt <deborah@connectholland.nl>
 */
class FilePermissionTaskTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if FilePermissionTask::getSubscribedEvents returns an array with a AccompliEvents::INSTALL_RELEASE key.
     */
    public function testGetSubscribedEvents()
    {
        $this->assertInternalType('array', FilePermissionTask::getSubscribedEvents());
        $this->assertArrayHasKey(AccompliEvents::INSTALL_RELEASE, FilePermissionTask::getSubscribedEvents());
    }

    /**
     * Tests if constructing a new FilePermissionTask sets the instance property.
     */
    public function testOnConstruct()
    {
        $paths = array('paths' => 'var/cache');
        $task = new FilePermissionTask($paths);

        $this->assertAttributeSame($paths, 'paths', $task);
    }

    /**
     * Tests if FilePermissionTask::onInstallReleaseUpdateFilePermissions throws an exception if the result is false.
     */
    public function testOnInstallReleaseUpdateFilePermissionsThrowsRuntimeExceptionWhenResultIsFalse()
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

        $workspaceMock = $this->getMockBuilder(Workspace::class)
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->once())
                ->method('getHost')
                ->willReturn($hostMock);

        $releaseMock = $this->getMockBuilder(Release::class)
                ->disableOriginalConstructor()
                ->getMock();
        $releaseMock->expects($this->exactly(1))
                ->method('getPath')
                ->willReturn('{workspace}/0.1.0');
        $releaseMock->expects($this->once())
                ->method('getWorkspace')
                ->willReturn($workspaceMock);

        $event = new InstallReleaseEvent($releaseMock);

        $this->setExpectedException(TaskRuntimeException::class, 'Failed updating the permissions for configured paths.');

        $paths = ['paths' => 'var/cache'];
        $task = new FilePermissionTask($paths);
        $task->onInstallReleaseUpdateFilePermissions($event, AccompliEvents::INSTALL_RELEASE, $eventDispatcherMock);
    }
}
