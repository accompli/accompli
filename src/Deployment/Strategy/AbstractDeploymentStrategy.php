<?php

namespace Accompli\Deployment\Strategy;

use Accompli\AccompliEvents;
use Accompli\Configuration\ConfigurationInterface;
use Accompli\DependencyInjection\ConfigurationAwareInterface;
use Accompli\DependencyInjection\EventDispatcherAwareInterface;
use Accompli\Deployment\Release;
use Accompli\Deployment\Workspace;
use Accompli\EventDispatcher\Event\DeployReleaseEvent;
use Accompli\EventDispatcher\Event\FailedEvent;
use Accompli\EventDispatcher\Event\HostEvent;
use Accompli\EventDispatcher\Event\PrepareDeployReleaseEvent;
use Accompli\EventDispatcher\Event\WorkspaceEvent;
use Accompli\EventDispatcher\EventDispatcherInterface;
use Exception;

/**
 * AbstractDeploymentStrategy.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
abstract class AbstractDeploymentStrategy implements DeploymentStrategyInterface, ConfigurationAwareInterface, EventDispatcherAwareInterface
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
    public function deploy($version, $stage)
    {
        $hosts = $this->configuration->getHostsByStage($stage);
        foreach ($hosts as $host) {
            $exception = null;

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
                        $deployReleaseEvent = new DeployReleaseEvent($release, $prepareDeployReleaseEvent->getCurrentRelease());
                        $this->eventDispatcher->dispatch(AccompliEvents::DEPLOY_RELEASE, $deployReleaseEvent);

                        $this->eventDispatcher->dispatch(AccompliEvents::DEPLOY_RELEASE_COMPLETE, $deployReleaseEvent);

                        continue;
                    }
                }
            } catch (Exception $exception) {
            }

            $failedEvent = new FailedEvent($this->eventDispatcher->getLastDispatchedEvent(), $exception);
            $this->eventDispatcher->dispatch(AccompliEvents::DEPLOY_RELEASE_FAILED, $failedEvent);
        }
    }
}
