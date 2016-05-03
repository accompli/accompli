<?php

namespace Accompli\Deployment\Strategy;

use Accompli\AccompliEvents;
use Accompli\Configuration\ConfigurationInterface;
use Accompli\Console\Logger\ConsoleLoggerInterface;
use Accompli\DependencyInjection\ConfigurationAwareInterface;
use Accompli\DependencyInjection\EventDispatcherAwareInterface;
use Accompli\DependencyInjection\LoggerAwareInterface;
use Accompli\Deployment\Release;
use Accompli\Deployment\Workspace;
use Accompli\EventDispatcher\Event\DeployReleaseEvent;
use Accompli\EventDispatcher\Event\FailedEvent;
use Accompli\EventDispatcher\Event\HostEvent;
use Accompli\EventDispatcher\Event\PrepareDeployReleaseEvent;
use Accompli\EventDispatcher\Event\WorkspaceEvent;
use Accompli\EventDispatcher\EventDispatcherInterface;
use Composer\Semver\Comparator;
use Exception;

/**
 * AbstractDeploymentStrategy.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
abstract class AbstractDeploymentStrategy implements DeploymentStrategyInterface, ConfigurationAwareInterface, EventDispatcherAwareInterface, LoggerAwareInterface
{
    /**
     * The configuration instance.
     *
     * @var ConfigurationInterface
     */
    protected $configuration;

    /**
     * The event dispatcher instance.
     *
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * The console logger instance.
     *
     * @var ConsoleLoggerInterface
     */
    protected $logger;

    /**
     * {@inheritdoc}
     */
    public function setConfiguration(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(ConsoleLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function deploy($version, $stage)
    {
        $successfulDeploy = true;

        $hosts = $this->configuration->getHostsByStage($stage);
        foreach ($hosts as $host) {
            $exception = null;

            $deployEventName = AccompliEvents::DEPLOY_RELEASE;
            $deployCompleteEventName = AccompliEvents::DEPLOY_RELEASE_COMPLETE;
            $deployFailedEventName = AccompliEvents::DEPLOY_RELEASE_FAILED;

            try {
                $this->eventDispatcher->dispatch(AccompliEvents::CREATE_CONNECTION, new HostEvent($host));

                $workspaceEvent = new WorkspaceEvent($host);
                $this->eventDispatcher->dispatch(AccompliEvents::GET_WORKSPACE, $workspaceEvent);

                $workspace = $workspaceEvent->getWorkspace();
                if ($workspace instanceof Workspace) {
                    $prepareDeployReleaseEvent = new PrepareDeployReleaseEvent($workspace, $version);
                    $this->eventDispatcher->dispatch(AccompliEvents::PREPARE_DEPLOY_RELEASE, $prepareDeployReleaseEvent);

                    $release = $prepareDeployReleaseEvent->getRelease();
                    if ($release instanceof Release) {
                        $currentRelease = $prepareDeployReleaseEvent->getCurrentRelease();
                        if ($currentRelease instanceof Release && Comparator::lessThan($release->getVersion(), $currentRelease->getVersion())) {
                            $deployEventName = AccompliEvents::ROLLBACK_RELEASE;
                            $deployCompleteEventName = AccompliEvents::ROLLBACK_RELEASE_COMPLETE;
                            $deployFailedEventName = AccompliEvents::ROLLBACK_RELEASE_FAILED;
                        }

                        $deployReleaseEvent = new DeployReleaseEvent($release, $currentRelease);
                        $this->eventDispatcher->dispatch($deployEventName, $deployReleaseEvent);

                        $this->eventDispatcher->dispatch($deployCompleteEventName, $deployReleaseEvent);

                        continue;
                    }
                }
            } catch (Exception $exception) {
            }

            $successfulDeploy = false;

            $failedEvent = new FailedEvent($this->eventDispatcher->getLastDispatchedEventName(), $this->eventDispatcher->getLastDispatchedEvent(), $exception);
            $this->eventDispatcher->dispatch($deployFailedEventName, $failedEvent);
        }

        return $successfulDeploy;
    }
}
