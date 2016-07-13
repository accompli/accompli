<?php

namespace Accompli\Test\Deployment\Strategy;

use Accompli\AccompliEvents;
use Accompli\Configuration\ConfigurationInterface;
use Accompli\Console\Logger\ConsoleLoggerInterface;
use Accompli\Deployment\Host;
use Accompli\Deployment\Release;
use Accompli\Deployment\Strategy\RemoteInstallStrategy;
use Accompli\Deployment\Workspace;
use Accompli\EventDispatcher\Event\FailedEvent;
use Accompli\EventDispatcher\Event\HostEvent;
use Accompli\EventDispatcher\Event\InstallReleaseEvent;
use Accompli\EventDispatcher\Event\PrepareReleaseEvent;
use Accompli\EventDispatcher\Event\WorkspaceEvent;
use Accompli\EventDispatcher\EventDispatcherInterface;
use Accompli\Exception\RuntimeException;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * RemoteInstallStrategyTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class RemoteInstallStrategyTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if RemoteInstallStrategy::install retrieves the hosts from the configuration through ConfigurationInterface::getHosts.
     */
    public function testInstallWithoutStageRetrievesHostsFromConfiguration()
    {
        $configurationMock = $this->getMockBuilder(ConfigurationInterface::class)
                ->getMock();
        $configurationMock->expects($this->once())
                ->method('getHosts')
                ->willReturn(array());
        $configurationMock->expects($this->never())
                ->method('getHostsByStage');

        $strategy = new RemoteInstallStrategy();
        $strategy->setConfiguration($configurationMock);

        $this->assertTrue($strategy->install('0.1.0'));
    }

    /**
     * Tests if RemoteInstallStrategy::install retrieves the hosts from the configuration through ConfigurationInterface::getHostsByStage.
     *
     * @depends testInstallWithoutStageRetrievesHostsFromConfiguration
     */
    public function testInstallWithStageRetrievesHostsByStageFromConfiguration()
    {
        $configurationMock = $this->getMockBuilder(ConfigurationInterface::class)
                ->getMock();
        $configurationMock->expects($this->once())
                ->method('getHosts')
                ->willReturn(array());
        $configurationMock->expects($this->once())
                ->method('getHostsByStage')
                ->with($this->equalTo(Host::STAGE_TEST))
                ->willReturn(array());

        $strategy = new RemoteInstallStrategy();
        $strategy->setConfiguration($configurationMock);

        $this->assertTrue($strategy->install('0.1.0', Host::STAGE_TEST));
    }

    /**
     * Tests if RemoteInstallStrategy::install dispatches all the events successfully.
     *
     * @depends testInstallWithStageRetrievesHostsByStageFromConfiguration
     */
    public function testInstallDispatchesEventsSuccessfully()
    {
        $hostMock = $this->getMockBuilder(Host::class)
                ->setConstructorArgs(array(Host::STAGE_TEST, 'local', null, __DIR__))
                ->getMock();

        $configurationMock = $this->getMockBuilder(ConfigurationInterface::class)
                ->getMock();
        $configurationMock->expects($this->once())
                ->method('getHostsByStage')
                ->with($this->equalTo(Host::STAGE_TEST))
                ->willReturn(array($hostMock));

        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();
        $eventDispatcherMock->expects($this->exactly(5))
                ->method('dispatch')
                ->withConsecutive(
                    array(
                        $this->equalTo(AccompliEvents::CREATE_CONNECTION),
                        $this->callback(function ($event) {
                            return ($event instanceof HostEvent);
                        }),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::PREPARE_WORKSPACE),
                        $this->callback(array($this, 'provideDispatchCallbackForWorkspaceEvent')),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::PREPARE_RELEASE),
                        $this->callback(array($this, 'provideDispatchCallbackForPrepareReleaseEvent')),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::INSTALL_RELEASE),
                        $this->callback(function ($event) {
                            return ($event instanceof InstallReleaseEvent);
                        }),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::INSTALL_RELEASE_COMPLETE),
                        $this->callback(function ($event) {
                            return ($event instanceof InstallReleaseEvent);
                        }),
                    )
                );

        $outputFormatterMock = $this->getMockBuilder(OutputFormatterInterface::class)
                ->getMock();

        $outputMock = $this->getMockBuilder(OutputInterface::class)
                ->getMock();
        $outputMock->expects($this->once())
                ->method('getFormatter')
                ->willReturn($outputFormatterMock);

        $loggerMock = $this->getMockBuilder(ConsoleLoggerInterface::class)
                ->getMock();
        $loggerMock->expects($this->once())
                ->method('getOutput')
                ->willReturn($outputMock);

        $strategy = new RemoteInstallStrategy();
        $strategy->setConfiguration($configurationMock);
        $strategy->setEventDispatcher($eventDispatcherMock);
        $strategy->setLogger($loggerMock);

        $this->assertTrue($strategy->install('0.1.0', Host::STAGE_TEST));
    }

    /**
     * Tests if RemoteInstallStrategy::install dispatches all the events successfully for multiple hosts.
     *
     * @depends testInstallDispatchesEventsSuccessfully
     */
    public function testInstallDispatchesEventsSuccessfullyForMultipleHosts()
    {
        $hostMock = $this->getMockBuilder(Host::class)
                ->setConstructorArgs(array(Host::STAGE_TEST, 'local', null, __DIR__))
                ->getMock();

        $configurationMock = $this->getMockBuilder(ConfigurationInterface::class)
                ->getMock();
        $configurationMock->expects($this->once())
                ->method('getHostsByStage')
                ->willReturn(array($hostMock, $hostMock));

        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();
        $eventDispatcherMock->expects($this->exactly(10))
                ->method('dispatch')
                ->withConsecutive(
                    array(
                        $this->equalTo(AccompliEvents::CREATE_CONNECTION),
                        $this->callback(function ($event) {
                            return ($event instanceof HostEvent);
                        }),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::PREPARE_WORKSPACE),
                        $this->callback(array($this, 'provideDispatchCallbackForWorkspaceEvent')),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::PREPARE_RELEASE),
                        $this->callback(array($this, 'provideDispatchCallbackForPrepareReleaseEvent')),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::INSTALL_RELEASE),
                        $this->callback(function ($event) {
                            return ($event instanceof InstallReleaseEvent);
                        }),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::INSTALL_RELEASE_COMPLETE),
                        $this->callback(function ($event) {
                            return ($event instanceof InstallReleaseEvent);
                        }),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::CREATE_CONNECTION),
                        $this->callback(function ($event) {
                            return ($event instanceof HostEvent);
                        }),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::PREPARE_WORKSPACE),
                        $this->callback(array($this, 'provideDispatchCallbackForWorkspaceEvent')),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::PREPARE_RELEASE),
                        $this->callback(array($this, 'provideDispatchCallbackForPrepareReleaseEvent')),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::INSTALL_RELEASE),
                        $this->callback(function ($event) {
                            return ($event instanceof InstallReleaseEvent);
                        }),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::INSTALL_RELEASE_COMPLETE),
                        $this->callback(function ($event) {
                            return ($event instanceof InstallReleaseEvent);
                        }),
                    )
                );

        $outputFormatterMock = $this->getMockBuilder(OutputFormatterInterface::class)
                ->getMock();

        $outputMock = $this->getMockBuilder(OutputInterface::class)
                ->getMock();
        $outputMock->expects($this->exactly(2))
                ->method('getFormatter')
                ->willReturn($outputFormatterMock);

        $loggerMock = $this->getMockBuilder(ConsoleLoggerInterface::class)
                ->getMock();
        $loggerMock->expects($this->exactly(2))
                ->method('getOutput')
                ->willReturn($outputMock);

        $strategy = new RemoteInstallStrategy();
        $strategy->setConfiguration($configurationMock);
        $strategy->setEventDispatcher($eventDispatcherMock);
        $strategy->setLogger($loggerMock);

        $this->assertTrue($strategy->install('0.1.0', Host::STAGE_TEST));
    }

    /**
     * Tests if RemoteInstallStrategy::install dispatches all the events untill after the PrepareWorkspaceEvent.
     *
     * @depends testInstallDispatchesEventsSuccessfully
     */
    public function testInstallDispatchesEventsSuccessfullyUntillAfterPrepareWorkspaceEvent()
    {
        $hostMock = $this->getMockBuilder(Host::class)
                ->setConstructorArgs(array(Host::STAGE_TEST, 'local', null, __DIR__))
                ->getMock();

        $configurationMock = $this->getMockBuilder(ConfigurationInterface::class)
                ->getMock();
        $configurationMock->expects($this->once())
                ->method('getHostsByStage')
                ->with($this->equalTo(Host::STAGE_TEST))
                ->willReturn(array($hostMock));

        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();
        $eventDispatcherMock->expects($this->once())
                ->method('getLastDispatchedEvent')
                ->willReturn(new Event());
        $eventDispatcherMock->expects($this->exactly(3))
                ->method('dispatch')
                ->withConsecutive(
                    array(
                        $this->equalTo(AccompliEvents::CREATE_CONNECTION),
                        $this->callback(function ($event) {
                            return ($event instanceof HostEvent);
                        }),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::PREPARE_WORKSPACE),
                        $this->callback(function ($event) {
                            return ($event instanceof WorkspaceEvent);
                        }),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::INSTALL_RELEASE_FAILED),
                        $this->callback(function ($event) {
                            return ($event instanceof FailedEvent && $event->getException() instanceof RuntimeException && $event->getException()->getMessage() === 'No task configured to initialize the workspace.');
                        }),
                    )
                );

        $outputFormatterMock = $this->getMockBuilder(OutputFormatterInterface::class)
                ->getMock();

        $outputMock = $this->getMockBuilder(OutputInterface::class)
                ->getMock();
        $outputMock->expects($this->once())
                ->method('getFormatter')
                ->willReturn($outputFormatterMock);

        $loggerMock = $this->getMockBuilder(ConsoleLoggerInterface::class)
                ->getMock();
        $loggerMock->expects($this->once())
                ->method('getOutput')
                ->willReturn($outputMock);

        $strategy = new RemoteInstallStrategy();
        $strategy->setConfiguration($configurationMock);
        $strategy->setEventDispatcher($eventDispatcherMock);
        $strategy->setLogger($loggerMock);

        $this->assertFalse($strategy->install('0.1.0', Host::STAGE_TEST));
    }

    /**
     * Tests if RemoteInstallStrategy::install dispatches all the events untill after the PrepareReleaseEvent.
     *
     * @depends testInstallDispatchesEventsSuccessfully
     */
    public function testInstallDispatchesEventsSuccessfullyUntillAfterPrepareReleaseEvent()
    {
        $hostMock = $this->getMockBuilder(Host::class)
                ->setConstructorArgs(array(Host::STAGE_TEST, 'local', null, __DIR__))
                ->getMock();

        $configurationMock = $this->getMockBuilder(ConfigurationInterface::class)
                ->getMock();
        $configurationMock->expects($this->once())
                ->method('getHostsByStage')
                ->with($this->equalTo(Host::STAGE_TEST))
                ->willReturn(array($hostMock));

        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();
        $eventDispatcherMock->expects($this->once())
                ->method('getLastDispatchedEvent')
                ->willReturn(new Event());
        $eventDispatcherMock->expects($this->exactly(4))
                ->method('dispatch')
                ->withConsecutive(
                    array(
                        $this->equalTo(AccompliEvents::CREATE_CONNECTION),
                        $this->callback(function ($event) {
                            return ($event instanceof HostEvent);
                        }),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::PREPARE_WORKSPACE),
                        $this->callback(array($this, 'provideDispatchCallbackForWorkspaceEvent')),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::PREPARE_RELEASE),
                        $this->callback(function ($event) {
                            return ($event instanceof PrepareReleaseEvent);
                        }),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::INSTALL_RELEASE_FAILED),
                        $this->callback(function ($event) {
                            return ($event instanceof FailedEvent && $event->getException() instanceof RuntimeException && $event->getException()->getMessage() === 'No task configured to install or create release version "0.1.0".');
                        }),
                    )
                );

        $outputFormatterMock = $this->getMockBuilder(OutputFormatterInterface::class)
                ->getMock();

        $outputMock = $this->getMockBuilder(OutputInterface::class)
                ->getMock();
        $outputMock->expects($this->once())
                ->method('getFormatter')
                ->willReturn($outputFormatterMock);

        $loggerMock = $this->getMockBuilder(ConsoleLoggerInterface::class)
                ->getMock();
        $loggerMock->expects($this->once())
                ->method('getOutput')
                ->willReturn($outputMock);

        $strategy = new RemoteInstallStrategy();
        $strategy->setConfiguration($configurationMock);
        $strategy->setEventDispatcher($eventDispatcherMock);
        $strategy->setLogger($loggerMock);

        $this->assertFalse($strategy->install('0.1.0', Host::STAGE_TEST));
    }

    /**
     * Provides the dispatch test callback for the PrepareWorkspaceEvent.
     *
     * @see testInstallDispatchesEventsSuccessfully
     *
     * @param Event $event
     *
     * @return bool
     */
    public function provideDispatchCallbackForWorkspaceEvent(Event $event)
    {
        $workspaceMock = $this->getMockBuilder(Workspace::class)
                ->setConstructorArgs(array($event->getHost()))
                ->getMock();

        $event->setWorkspace($workspaceMock);

        return ($event instanceof WorkspaceEvent);
    }

    /**
     * Provides the dispatch test callback for the PrepareReleaseEvent.
     *
     * @see testInstallDispatchesEventsSuccessfully
     *
     * @param Event $event
     *
     * @return bool
     */
    public function provideDispatchCallbackForPrepareReleaseEvent(Event $event)
    {
        $releaseMock = $this->getMockBuilder(Release::class)
                ->setConstructorArgs(array($event->getVersion()))
                ->getMock();

        $event->setRelease($releaseMock);

        return ($event instanceof PrepareReleaseEvent);
    }
}
