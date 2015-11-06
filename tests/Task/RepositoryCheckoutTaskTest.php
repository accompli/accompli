<?php

namespace Accompli\Test;

use Accompli\AccompliEvents;
use Accompli\Chrono\Process\ProcessExecutionResult;
use Accompli\EventDispatcher\Event\PrepareReleaseEvent;
use Accompli\Task\RepositoryCheckoutTask;
use PHPUnit_Framework_TestCase;

/**
 * RepositoryCheckoutTaskTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class RepositoryCheckoutTaskTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if RepositoryCheckoutTask::getSubscribedEvents returns an array with at least a AccompliEvents::PREPARE_RELEASE key.
     */
    public function testGetSubscribedEvents()
    {
        $this->assertInternalType('array', RepositoryCheckoutTask::getSubscribedEvents());
        $this->assertArrayHasKey(AccompliEvents::PREPARE_RELEASE, RepositoryCheckoutTask::getSubscribedEvents());
    }

    /**
     * Tests if constructing a new RepositoryCheckoutTask sets the instance properties.
     */
    public function testConstruct()
    {
        $task = new RepositoryCheckoutTask('https://github.com/accompli/accompli.git');

        $this->assertAttributeSame('https://github.com/accompli/accompli.git', 'repositoryUrl', $task);
    }

    /**
     * Tests if RepositoryCheckoutTask::onPrepareReleaseConstructReleaseInstance constructs a new Release instance and sets it on the PrepareReleaseEvent.
     *
     * @depends testConstruct
     */
    public function testOnPrepareReleaseConstructReleaseInstance()
    {
        $workspaceMock = $this->getMockBuilder('Accompli\Deployment\Workspace')
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->once())->method('addRelease');

        $event = new PrepareReleaseEvent($workspaceMock, '0.1.0');

        $task = new RepositoryCheckoutTask('https://github.com/accompli/accompli.git');
        $task->onPrepareReleaseConstructReleaseInstance($event);

        $this->assertInstanceOf('Accompli\Deployment\Release', $event->getRelease());
        $this->assertSame('0.1.0', $event->getRelease()->getVersion());
    }

    /**
     * Tests if RepositoryCheckoutTask::onPrepareReleaseConstructReleaseInstance does nothing when a Release instance already exists within the PrepareReleaseEvent.
     *
     * @depends testConstruct
     */
    public function testOnPrepareReleaseConstructReleaseInstanceDoesNothingWhenReleaseInstanceExists()
    {
        $workspaceMock = $this->getMockBuilder('Accompli\Deployment\Workspace')
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->never())->method('addRelease');

        $releaseMock = $this->getMockBuilder('Accompli\Deployment\Release')
                ->disableOriginalConstructor()
                ->getMock();

        $event = new PrepareReleaseEvent($workspaceMock, '0.1.0');
        $event->setRelease($releaseMock);

        $task = new RepositoryCheckoutTask('https://github.com/accompli/accompli.git');
        $task->onPrepareReleaseConstructReleaseInstance($event);

        $this->assertInstanceOf('Accompli\Deployment\Release', $event->getRelease());
    }

    /**
     * Tests if RepositoryCheckoutTask::onPrepareReleaseCheckoutRepository successfully checks out a repository.
     *
     * @depends testOnPrepareReleaseConstructReleaseInstance
     */
    public function testOnPrepareReleaseCheckoutRepository()
    {
        $eventDispatcherMock = $this->getMockBuilder('Accompli\EventDispatcher\EventDispatcherInterface')->getMock();
        $eventDispatcherMock->expects($this->exactly(2))->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder('Accompli\Deployment\Connection\ConnectionAdapterInterface')->getMock();
        $connectionAdapterMock->expects($this->exactly(2))
                ->method('executeCommand')
                ->willReturn(new ProcessExecutionResult(0, '', ''));

        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())->method('hasConnection')->willReturn(true);
        $hostMock->expects($this->once())->method('getConnection')->willReturn($connectionAdapterMock);

        $workspaceMock = $this->getMockBuilder('Accompli\Deployment\Workspace')
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->once())->method('getHost')->willReturn($hostMock);

        $releaseMock = $this->getMockBuilder('Accompli\Deployment\Release')
                ->disableOriginalConstructor()
                ->getMock();
        $releaseMock->expects($this->once())->method('getWorkspace')->willReturn($workspaceMock);
        $releaseMock->expects($this->exactly(2))->method('getVersion')->willReturn('0.1.0');

        $event = new PrepareReleaseEvent($workspaceMock, '0.1.0');
        $event->setRelease($releaseMock);

        $task = new RepositoryCheckoutTask('https://github.com/accompli/accompli.git');
        $task->onPrepareReleaseCheckoutRepository($event, AccompliEvents::PREPARE_RELEASE, $eventDispatcherMock);
    }

    /**
     * Tests if RepositoryCheckoutTask::onPrepareReleaseCheckoutRepository throws a RuntimeException when a Repository::checkout fails.
     *
     * @depends testOnPrepareReleaseConstructReleaseInstance
     *
     * @expectedException        RuntimeException
     * @expectedExceptionMessage Checkout of repository "https://github.com/accompli/accompli.git" for version "0.1.0" failed.
     */
    public function testOnPrepareReleaseCheckoutRepositoryThrowsRuntimeException()
    {
        $eventDispatcherMock = $this->getMockBuilder('Accompli\EventDispatcher\EventDispatcherInterface')->getMock();
        $eventDispatcherMock->expects($this->once())->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder('Accompli\Deployment\Connection\ConnectionAdapterInterface')->getMock();
        $connectionAdapterMock->expects($this->exactly(2))
                ->method('executeCommand')
                ->willReturnOnConsecutiveCalls(
                        new ProcessExecutionResult(0, '', ''),
                        new ProcessExecutionResult(1, '', '')
                );

        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())->method('hasConnection')->willReturn(true);
        $hostMock->expects($this->once())->method('getConnection')->willReturn($connectionAdapterMock);

        $workspaceMock = $this->getMockBuilder('Accompli\Deployment\Workspace')
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->once())->method('getHost')->willReturn($hostMock);

        $releaseMock = $this->getMockBuilder('Accompli\Deployment\Release')
                ->disableOriginalConstructor()
                ->getMock();
        $releaseMock->expects($this->once())->method('getWorkspace')->willReturn($workspaceMock);
        $releaseMock->expects($this->exactly(3))->method('getVersion')->willReturn('0.1.0');

        $event = new PrepareReleaseEvent($workspaceMock, '0.1.0');
        $event->setRelease($releaseMock);

        $task = new RepositoryCheckoutTask('https://github.com/accompli/accompli.git');
        $task->onPrepareReleaseCheckoutRepository($event, AccompliEvents::PREPARE_RELEASE, $eventDispatcherMock);
    }
}
