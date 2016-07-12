<?php

namespace Accompli\Deployment\Strategy;

use Accompli\AccompliEvents;
use Accompli\Console\Helper\Title;
use Accompli\Deployment\Release;
use Accompli\Deployment\Workspace;
use Accompli\EventDispatcher\Event\FailedEvent;
use Accompli\EventDispatcher\Event\HostEvent;
use Accompli\EventDispatcher\Event\InstallReleaseEvent;
use Accompli\EventDispatcher\Event\PrepareReleaseEvent;
use Accompli\EventDispatcher\Event\WorkspaceEvent;
use Accompli\Exception\RuntimeException;
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

            $title = new Title($this->logger->getOutput(), sprintf('Installing release "%s" to "%s":', $version, $host->getHostname()));
            $title->render();

            try {
                $this->eventDispatcher->dispatch(AccompliEvents::CREATE_CONNECTION, new HostEvent($host));

                $workspaceEvent = new WorkspaceEvent($host);
                $this->eventDispatcher->dispatch(AccompliEvents::PREPARE_WORKSPACE, $workspaceEvent);

                $workspace = $workspaceEvent->getWorkspace();
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

                    throw new RuntimeException(sprintf('No task configured to install or create release version "%s".', $version));
                }

                throw new RuntimeException('No task configured to initialize the workspace.');
            } catch (Exception $exception) {
            }

            $successfulInstall = false;

            $failedEvent = new FailedEvent($this->eventDispatcher->getLastDispatchedEventName(), $this->eventDispatcher->getLastDispatchedEvent(), $exception);
            $this->eventDispatcher->dispatch(AccompliEvents::INSTALL_RELEASE_FAILED, $failedEvent);
        }

        return $successfulInstall;
    }
}
