<?php

namespace Accompli\Deployment\Strategy;

use Accompli\AccompliEvents;
use Accompli\Deployment\Release;
use Accompli\Deployment\Workspace;
use Accompli\EventDispatcher\Event\FailedEvent;
use Accompli\EventDispatcher\Event\HostEvent;
use Accompli\EventDispatcher\Event\InstallReleaseEvent;
use Accompli\EventDispatcher\Event\PrepareReleaseEvent;
use Accompli\EventDispatcher\Event\PrepareWorkspaceEvent;
use Exception;

/**
 * RemoteInstallStrategy.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class RemoteInstallStrategy extends AbstractDeploymentStrategy
{
    /**
     * {@inheritdoc}
     */
    public function install($version, $stage = null)
    {
        $successfulInstall = true;

        $hosts = $this->configuration->getHosts();
        if ($stage !== null) {
            $hosts = $this->configuration->getHostsByStage($stage);
        }

        foreach ($hosts as $host) {
            $exception = null;

            try {
                $this->eventDispatcher->dispatch(AccompliEvents::CREATE_CONNECTION, new HostEvent($host));

                $prepareWorkspaceEvent = new PrepareWorkspaceEvent($host);
                $this->eventDispatcher->dispatch(AccompliEvents::PREPARE_WORKSPACE, $prepareWorkspaceEvent);

                $workspace = $prepareWorkspaceEvent->getWorkspace();
                if ($workspace instanceof Workspace) {
                    $prepareReleaseEvent = new PrepareReleaseEvent($workspace, $version);
                    $this->eventDispatcher->dispatch(AccompliEvents::PREPARE_RELEASE, $prepareReleaseEvent);

                    $release = $prepareReleaseEvent->getRelease();
                    if ($release instanceof Release) {
                        $installReleaseEvent = new InstallReleaseEvent($release);
                        $this->eventDispatcher->dispatch(AccompliEvents::INSTALL_RELEASE, $installReleaseEvent);

                        $this->eventDispatcher->dispatch(AccompliEvents::INSTALL_RELEASE_COMPLETE, $installReleaseEvent);

                        continue;
                    }
                }
            } catch (Exception $exception) {
            }

            $successfulInstall = false;

            $failedEvent = new FailedEvent($this->eventDispatcher->getLastDispatchedEvent(), $exception);
            $this->eventDispatcher->dispatch(AccompliEvents::INSTALL_RELEASE_FAILED, $failedEvent);
        }

        return $successfulInstall;
    }
}
