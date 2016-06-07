<?php

namespace Accompli\Test\Deployment\Strategy;

use Accompli\AccompliEvents;
use Accompli\Configuration\ConfigurationInterface;
use Accompli\Console\Logger\ConsoleLoggerInterface;
use Accompli\Deployment\Host;
use Accompli\Deployment\Release;
use Accompli\Deployment\Strategy\AbstractDeploymentStrategy;
use Accompli\Deployment\Workspace;
use Accompli\EventDispatcher\Event\DeployReleaseEvent;
use Accompli\EventDispatcher\Event\FailedEvent;
use Accompli\EventDispatcher\Event\HostEvent;
use Accompli\EventDispatcher\Event\PrepareDeployReleaseEvent;
use Accompli\EventDispatcher\Event\WorkspaceEvent;
use Accompli\EventDispatcher\EventDispatcherInterface;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * AbstractDeploymentStrategyTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class AbstractDeploymentStrategyTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if AbstractDeploymentStrategy::setConfiguration sets the configuration property of the class.
     */
    public function testSetConfiguration()
    {
        $configurationMock = $this->getMockBuilder(ConfigurationInterface::class)
                ->getMock();

        $deploymentStrategy = $this->getMockBuilder(AbstractDeploymentStrategy::class)
                ->getMockForAbstractClass();
        $deploymentStrategy->setConfiguration($configurationMock);

        $this->assertAttributeSame($configurationMock, 'configuration', $deploymentStrategy);
    }

    /**
     * Tests if AbstractDeploymentStrategy::setEventDispatcher sets the event dispatcher property of the class.
     */
    public function testSetEventDispatcher()
    {
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();

        $deploymentStrategy = $this->getMockBuilder(AbstractDeploymentStrategy::class)
                ->getMockForAbstractClass();
        $deploymentStrategy->setEventDispatcher($eventDispatcherMock);

        $this->assertAttributeSame($eventDispatcherMock, 'eventDispatcher', $deploymentStrategy);
    }

    /**
     * Tests if AbstractDeploymentStrategy::setLogger sets the logger property of the class.
     */
    public function testSetLogger()
    {
        $loggerMock = $this->getMockBuilder(ConsoleLoggerInterface::class)
                ->getMock();

        $deploymentStrategy = $this->getMockBuilder(AbstractDeploymentStrategy::class)
                ->getMockForAbstractClass();
        $deploymentStrategy->setLogger($loggerMock);

        $this->assertAttributeSame($loggerMock, 'logger', $deploymentStrategy);
    }

    /**
     * Tests if AbstractDeploymentStrategy::deploy dispatches all the events successfully.
     *
     * @depends testSetConfiguration
     * @depends testSetEventDispatcher
     */
    public function testDeployDispatchesEventsSuccessfully()
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
                        $this->equalTo(AccompliEvents::GET_WORKSPACE),
                        $this->callback(array($this, 'provideDispatchCallbackForWorkspaceEvent')),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::PREPARE_DEPLOY_RELEASE),
                        $this->callback(array($this, 'provideDispatchCallbackForPrepareDeployReleaseEvent')),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::DEPLOY_RELEASE),
                        $this->callback(function ($event) {
                            return ($event instanceof DeployReleaseEvent);
                        }),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::DEPLOY_RELEASE_COMPLETE),
                        $this->callback(function ($event) {
                            return ($event instanceof DeployReleaseEvent);
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

        $deploymentStrategy = $this->getMockBuilder(AbstractDeploymentStrategy::class)
                ->getMockForAbstractClass();
        $deploymentStrategy->setConfiguration($configurationMock);
        $deploymentStrategy->setEventDispatcher($eventDispatcherMock);
        $deploymentStrategy->setLogger($loggerMock);

        $this->assertTrue($deploymentStrategy->deploy('0.1.0', Host::STAGE_TEST));
    }

    /**
     * Tests if AbstractDeploymentStrategy::deploy dispatches all the events successfully for multiple hosts.
     *
     * @depends testDeployDispatchesEventsSuccessfully
     */
    public function testDeployDispatchesEventsSuccessfullyForMultipleHosts()
    {
        $hostMock = $this->getMockBuilder(Host::class)
                ->setConstructorArgs(array(Host::STAGE_TEST, 'local', null, __DIR__))
                ->getMock();

        $configurationMock = $this->getMockBuilder(ConfigurationInterface::class)
                ->getMock();
        $configurationMock->expects($this->once())
                ->method('getHostsByStage')
                ->with($this->equalTo(Host::STAGE_TEST))
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
                        $this->equalTo(AccompliEvents::GET_WORKSPACE),
                        $this->callback(array($this, 'provideDispatchCallbackForWorkspaceEvent')),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::PREPARE_DEPLOY_RELEASE),
                        $this->callback(array($this, 'provideDispatchCallbackForPrepareDeployReleaseEvent')),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::DEPLOY_RELEASE),
                        $this->callback(function ($event) {
                            return ($event instanceof DeployReleaseEvent);
                        }),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::DEPLOY_RELEASE_COMPLETE),
                        $this->callback(function ($event) {
                            return ($event instanceof DeployReleaseEvent);
                        }),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::CREATE_CONNECTION),
                        $this->callback(function ($event) {
                            return ($event instanceof HostEvent);
                        }),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::GET_WORKSPACE),
                        $this->callback(array($this, 'provideDispatchCallbackForWorkspaceEvent')),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::PREPARE_DEPLOY_RELEASE),
                        $this->callback(array($this, 'provideDispatchCallbackForPrepareDeployReleaseEvent')),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::DEPLOY_RELEASE),
                        $this->callback(function ($event) {
                            return ($event instanceof DeployReleaseEvent);
                        }),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::DEPLOY_RELEASE_COMPLETE),
                        $this->callback(function ($event) {
                            return ($event instanceof DeployReleaseEvent);
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

        $deploymentStrategy = $this->getMockBuilder(AbstractDeploymentStrategy::class)
                ->getMockForAbstractClass();
        $deploymentStrategy->setConfiguration($configurationMock);
        $deploymentStrategy->setEventDispatcher($eventDispatcherMock);
        $deploymentStrategy->setLogger($loggerMock);

        $this->assertTrue($deploymentStrategy->deploy('0.1.0', Host::STAGE_TEST));
    }

    /**
     * Tests if AbstractDeploymentStrategy::deploy dispatches all the events untill after the WorkspaceEvent.
     *
     * @depends testDeployDispatchesEventsSuccessfully
     */
    public function testDeployDispatchesEventsSuccessfullyUntillAfterWorkspaceEvent()
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
                        $this->equalTo(AccompliEvents::GET_WORKSPACE),
                        $this->callback(function ($event) {
                            return ($event instanceof WorkspaceEvent);
                        }),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::DEPLOY_RELEASE_FAILED),
                        $this->callback(function ($event) {
                            return ($event instanceof FailedEvent);
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

        $deploymentStrategy = $this->getMockBuilder(AbstractDeploymentStrategy::class)
                ->getMockForAbstractClass();
        $deploymentStrategy->setConfiguration($configurationMock);
        $deploymentStrategy->setEventDispatcher($eventDispatcherMock);
        $deploymentStrategy->setLogger($loggerMock);

        $this->assertFalse($deploymentStrategy->deploy('0.1.0', Host::STAGE_TEST));
    }

    /**
     * Tests if AbstractDeploymentStrategy::deploy dispatches all the events untill after the PrepareDeployReleaseEvent.
     *
     * @depends testDeployDispatchesEventsSuccessfully
     */
    public function testDeployDispatchesEventsSuccessfullyUntillAfterPrepareDeployReleaseEvent()
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
                        $this->equalTo(AccompliEvents::GET_WORKSPACE),
                        $this->callback(array($this, 'provideDispatchCallbackForWorkspaceEvent')),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::PREPARE_DEPLOY_RELEASE),
                        $this->callback(function ($event) {
                            return ($event instanceof PrepareDeployReleaseEvent);
                        }),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::DEPLOY_RELEASE_FAILED),
                        $this->callback(function ($event) {
                            return ($event instanceof FailedEvent);
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

        $deploymentStrategy = $this->getMockBuilder(AbstractDeploymentStrategy::class)
                ->getMockForAbstractClass();
        $deploymentStrategy->setConfiguration($configurationMock);
        $deploymentStrategy->setEventDispatcher($eventDispatcherMock);
        $deploymentStrategy->setLogger($loggerMock);

        $this->assertFalse($deploymentStrategy->deploy('0.1.0', Host::STAGE_TEST));
    }

    /**
     * Tests if AbstractDeploymentStrategy::deploy dispatches all the rollback events successfully.
     *
     * @depends testDeployDispatchesEventsSuccessfully
     */
    public function testDeployDispatchesRollbackEventsSuccessfully()
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
                        $this->equalTo(AccompliEvents::GET_WORKSPACE),
                        $this->callback(array($this, 'provideDispatchCallbackForWorkspaceEvent')),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::PREPARE_DEPLOY_RELEASE),
                        $this->callback(array($this, 'provideDispatchCallbackForRollbackPrepareDeployReleaseEvent')),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::ROLLBACK_RELEASE),
                        $this->callback(function ($event) {
                            return ($event instanceof DeployReleaseEvent);
                        }),
                    ),
                    array(
                        $this->equalTo(AccompliEvents::ROLLBACK_RELEASE_COMPLETE),
                        $this->callback(function ($event) {
                            return ($event instanceof DeployReleaseEvent);
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

        $deploymentStrategy = $this->getMockBuilder(AbstractDeploymentStrategy::class)
                ->getMockForAbstractClass();
        $deploymentStrategy->setConfiguration($configurationMock);
        $deploymentStrategy->setEventDispatcher($eventDispatcherMock);
        $deploymentStrategy->setLogger($loggerMock);

        $this->assertTrue($deploymentStrategy->deploy('0.1.0', Host::STAGE_TEST));
    }

    /**
     * Provides the dispatch test callback for the WorkspaceEvent.
     *
     * @see testDeployDispatchesEventsSuccessfully
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
     * Provides the dispatch test callback for the PrepareDeployReleaseEvent.
     *
     * @see testDeployDispatchesEventsSuccessfully
     *
     * @param Event $event
     *
     * @return bool
     */
    public function provideDispatchCallbackForPrepareDeployReleaseEvent(Event $event)
    {
        $releaseMock = $this->getMockBuilder(Release::class)
                ->setConstructorArgs(array($event->getVersion()))
                ->getMock();

        $event->setRelease($releaseMock);

        return ($event instanceof PrepareDeployReleaseEvent);
    }

    /**
     * Provides the dispatch test callback for the PrepareDeployReleaseEvent for the rollback scenario.
     *
     * @see testDeployDispatchesEventsSuccessfully
     *
     * @param Event $event
     *
     * @return bool
     */
    public function provideDispatchCallbackForRollbackPrepareDeployReleaseEvent(Event $event)
    {
        $releaseMock = $this->getMockBuilder(Release::class)
                ->disableOriginalConstructor()
                ->getMock();
        $releaseMock->expects($this->any())
                ->method('getVersion')
                ->willReturn($event->getVersion());

        $currentReleaseMock = $this->getMockBuilder(Release::class)
                ->disableOriginalConstructor()
                ->getMock();
        $currentReleaseMock->expects($this->any())
                ->method('getVersion')
                ->willReturn('0.1.1');

        $event->setRelease($releaseMock);
        $event->setCurrentRelease($currentReleaseMock);

        return ($event instanceof PrepareDeployReleaseEvent);
    }
}
