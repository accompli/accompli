<?php

namespace Accompli\Test\Task;

use Accompli\AccompliEvents;
use Accompli\Deployment\Connection\ConnectionAdapterInterface;
use Accompli\Deployment\Host;
use Accompli\Deployment\Release;
use Accompli\Deployment\Workspace;
use Accompli\EventDispatcher\Event\PrepareReleaseEvent;
use Accompli\EventDispatcher\EventDispatcherInterface;
use Accompli\Exception\TaskRuntimeException;
use Accompli\Task\LinkTask;
use PHPUnit_Framework_TestCase;

/**
 * LinkTaskTest.
 *
 * @author Reyo Stallenberg <reyo@connectholland.nl>
 */
class LinkTaskTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if LinkTask::getSubscribedEvents returns an array with a AccompliEvents::PREPARE_RELEASE key.
     */
    public function testGetSubscribedEvents()
    {
        $this->assertInternalType('array', LinkTask::getSubscribedEvents());
        $this->assertArrayHasKey(AccompliEvents::PREPARE_RELEASE, LinkTask::getSubscribedEvents());
    }

    /**
     * Tests if constructing a new LinkTask sets the instance property.
     */
    public function testOnConstruct()
    {
        $links = array('var/log', 'log');
        $task = new LinkTask($links);

        $this->assertAttributeSame($links, 'links', $task);
    }

    /**
     * Tests if LinkTask::onPrepareReleaseCreateLinks throws an exception if the result is false.
     */
    public function testOnPrepareReleaseCreateLinksThrowsRuntimeException()
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

        $event = new PrepareReleaseEvent($workspaceMock, '0.1.0');
        $event->setRelease($releaseMock);

        $this->setExpectedException(TaskRuntimeException::class, 'Failed linking data directories for the configured paths.');

        $links = array('invalid' => false);

        $task = new LinkTask($links);
        $task->onPrepareReleaseCreateLinks($event, AccompliEvents::PREPARE_RELEASE, $eventDispatcherMock);
    }

    /**
     * Tests if LinkTask::onPrepareReleaseCreateLinks works if the correct settings are applied.
     */
    public function testOnPrepareReleaseCreateLinks()
    {
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();

        $connectionAdapterMock = $this->getMockBuilder(ConnectionAdapterInterface::class)
                ->getMock();
        $connectionAdapterMock->expects($this->exactly(2))
                ->method('isLink')
                ->willReturn(true);
        $connectionAdapterMock->expects($this->exactly(2))
                ->method('link')
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

        $releaseMock = $this->getMockBuilder(Release::class)
                ->disableOriginalConstructor()
                ->getMock();
        $releaseMock->expects($this->exactly(1))
                ->method('getPath')
                ->willReturn('{workspace}/0.1.0');

        $event = new PrepareReleaseEvent($workspaceMock, '0.1.0');
        $event->setRelease($releaseMock);

        $links = array('var/log', 'log');

        $task = new LinkTask($links);
        $task->onPrepareReleaseCreateLinks($event, AccompliEvents::PREPARE_RELEASE, $eventDispatcherMock);
    }
}
