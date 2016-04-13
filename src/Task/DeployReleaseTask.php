<?php

namespace Accompli\Task;

use Accompli\AccompliEvents;
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
            $releaseRealPath = $connection->readLink($releasePath);
            if (strpos($releaseRealPath, $workspace->getReleasesDirectory()) === 0) {
                $currentRelease = new Release(substr($releaseRealPath, strlen($workspace->getReleasesDirectory())));
                $workspace->addRelease($currentRelease);

                $context = array('currentReleaseVersion' => $currentRelease->getVersion());
                $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, 'Detected release version "{currentReleaseVersion}" currently deployed.', $eventName, $this, $context));

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
    public function onDeployOrRollbackReleaseLinkRelease(DeployReleaseEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        $release = $event->getRelease();
        $host = $release->getWorkspace()->getHost();
        $connection = $this->ensureConnection($host);

        $releasePath = $host->getPath().'/'.$host->getStage();

        $context = array('linkTarget' => $releasePath, 'releaseVersion' => $release->getVersion(), 'event.task.action' => TaskInterface::ACTION_IN_PROGRESS);
        $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::NOTICE, 'Linking "{linkTarget}" to release "{releaseVersion}".', $eventName, $this, $context));

        if ($connection->isLink($releasePath) === false || $connection->readLink($releasePath) !== $release->getPath()) {
            if ($connection->isLink($releasePath)) {
                $connection->delete($releasePath, false);
            }

            if ($connection->link($release->getPath(), $releasePath)) {
                $context['event.task.action'] = TaskInterface::ACTION_COMPLETED;
                $context['output.resetLine'] = true;

                $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::NOTICE, 'Linked "{linkTarget}" to release "{releaseVersion}".', $eventName, $this, $context));
            } else {
                $context['event.task.action'] = TaskInterface::ACTION_FAILED;
                $context['output.resetLine'] = true;

                $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::NOTICE, 'Linking "{linkTarget}" to release "{releaseVersion}" failed.', $eventName, $this, $context));

                throw new RuntimeException(sprintf('Linking "%s" to release "%s" failed.', $context['linkTarget'], $context['releaseVersion']));
            }
        } else {
            $context['event.task.action'] = TaskInterface::ACTION_COMPLETED;
            $context['output.resetLine'] = true;

            $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::NOTICE, 'Link "{linkTarget}" to release "{releaseVersion}" already exists.', $eventName, $this, $context));
        }
    }
}
