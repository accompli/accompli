<?php

namespace Accompli\Deployment\Strategy;

use Accompli\AccompliEvents;
use Accompli\Deployment\Release;
use Accompli\Deployment\Workspace;
use Accompli\Event\FailedEvent;
use Accompli\Event\InstallReleaseEvent;
use Accompli\Event\PrepareReleaseEvent;
use Accompli\Event\PrepareWorkspaceEvent;

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
        $hosts = $this->configuration->getHosts();
        if ($stage !== null) {
            $hosts = $this->configuration->getHostsByStage($stage);
        }

        foreach ($hosts as $host) {
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

                    return;
                }
            }

            $this->eventDispatcher->dispatch(AccompliEvents::INSTALL_RELEASE_FAILED, new FailedEvent());
        }
    }
}
