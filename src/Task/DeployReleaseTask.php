<?php

namespace Accompli\Task;

use Accompli\AccompliEvents;
use Accompli\Deployment\Connection\ConnectionAdapterInterface;
use Accompli\Deployment\Release;
use Accompli\EventDispatcher\Event\DeployReleaseEvent;
use Accompli\EventDispatcher\Event\LogEvent;
use Accompli\EventDispatcher\Event\PrepareDeployReleaseEvent;
use Accompli\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LogLevel;
use RuntimeException;

/**
 * DeployReleaseTask.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class DeployReleaseTask extends AbstractConnectedTask
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            AccompliEvents::PREPARE_DEPLOY_RELEASE => array(
                array('onPrepareDeployReleaseConstructReleaseInstances', 10),
            ),
            AccompliEvents::DEPLOY_RELEASE => array(
                array('onDeployOrRollbackReleaseLinkRelease', 0),
            ),
            AccompliEvents::ROLLBACK_RELEASE => array(
                array('onDeployOrRollbackReleaseLinkRelease', 0),
            ),
        );
    }

    /**
     * Constructs a new Release instance for the release being deployed and the Release currently deployed (when available) sets the instances on the event.
     *
     * @param PrepareDeployReleaseEvent $event
     * @param string                    $eventName
     * @param EventDispatcherInterface  $eventDispatcher
     *
     * @throws RuntimeException when the version selected for deployment is not installed within the workspace.
     */
    public function onPrepareDeployReleaseConstructReleaseInstances(PrepareDeployReleaseEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        $workspace = $event->getWorkspace();
        $host = $workspace->getHost();
        $connection = $this->ensureConnection($host);

        $release = new Release($event->getVersion());
        $workspace->addRelease($release);
        if ($connection->isDirectory($release->getPath()) === false) {
            throw new RuntimeException(sprintf('The release "%s" is not installed within the workspace.', $release->getVersion()));
        }
        $event->setRelease($release);

        $currentRelease = null;
        $releasePath = $host->getPath().'/'.$host->getStage();
        if ($connection->isLink($releasePath)) {
            $releaseRealPath = $this->getRealPath($connection, $releasePath);
            if (strpos($releaseRealPath, $workspace->getReleasesDirectory()) === 0) {
                $currentRelease = new Release(substr($releaseRealPath, strlen($workspace->getReleasesDirectory()) + 1));
                $workspace->addRelease($release);

                $context = array('currentReleaseVersion' => $currentRelease->getVersion());
                $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::DEBUG, 'Detected release version "{currentReleaseVersion}" currently deployed.', $eventName, $this, $context));

                $event->setCurrentRelease($currentRelease);
            }
        }
    }

    /**
     * Links the release being deployed.
     *
     * @param DeployReleaseEvent       $event
     * @param string                   $eventName
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function onDeployReleaseLinkRelease(DeployReleaseEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        $release = $event->getRelease();
        $host = $release->getWorkspace()->getHost();
        $connection = $this->ensureConnection($host);

        $releasePath = $host->getPath().'/'.$host->getStage();

        $context = array('linkTarget' => $releasePath, 'releaseVersion' => $release->getVersion(), 'event.task.action' => TaskInterface::ACTION_IN_PROGRESS);
        $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, 'Linking "{linkTarget}" to release "{releaseVersion}".', $eventName, $this, $context));

        if ($connection->isLink($releasePath) === false || $this->getRealPath($connection, $releasePath) !== $release->getPath()) {
            if ($connection->isLink($releasePath)) {
                $connection->delete($releasePath, false);
            }

            if ($connection->link($release->getPath(), $releasePath)) {
                $context['event.task.action'] = TaskInterface::ACTION_COMPLETED;
                $context['output.resetLine'] = true;

                $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, 'Linked "{linkTarget}" to release "{releaseVersion}".', $eventName, $this, $context));
            } else {
                $context['event.task.action'] = TaskInterface::ACTION_FAILED;
                $context['output.resetLine'] = true;

                $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, 'Linking "{linkTarget}" to release "{releaseVersion}" failed.', $eventName, $this, $context));

                throw new RuntimeException(sprintf('Linking "%s" to release "%s" failed.', $context['linkTarget'], $context['releaseVersion']));
            }
        } else {
            $context['event.task.action'] = TaskInterface::ACTION_COMPLETED;
            $context['output.resetLine'] = true;

            $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, 'Link "{linkTarget}" to release "{releaseVersion}" already exists.', $eventName, $this, $context));
        }
    }

    /**
     * Returns the canonicalized absolute path of a link.
     *
     * Note: This should be replaced by the release manifest solution.
     *
     * @see https://github.com/accompli/accompli/issues/70
     *
     * @param ConnectionAdapterInterface $connection
     * @param string                     $path
     *
     * @return string
     */
    private function getRealPath(ConnectionAdapterInterface $connection, $path)
    {
        $connection->changeWorkingDirectory($path);

        return $connection->getWorkingDirectory();
    }
}
