<?php

namespace Accompli\Task;

use Accompli\AccompliEvents;
use Accompli\Deployment\Connection\ConnectionAdapterInterface;
use Accompli\EventDispatcher\Event\DeployReleaseEvent;
use Accompli\EventDispatcher\Event\LogEvent;
use Accompli\EventDispatcher\Event\WorkspaceEvent;
use Accompli\EventDispatcher\EventDispatcherInterface;
use InvalidArgumentException;
use Psr\Log\LogLevel;

/**
 * Clean up old releases.
 *
 * @author Jaap Romijn <jaap@connectholland.nl>
 */
class ReleaseCleanupTask extends AbstractConnectedTask
{
    /**
     * The amount of versions to keep.
     *
     * @var number
     */
    private $keepAmount;

    /**
     * Return the amount of versions to keep.
     *
     * @return number
     */
    public function getKeepAmount()
    {
        return $this->keepAmount;
    }

    /**
     * Set the amount of versions to keep.
     *
     * @param number $keepAmount
     *
     * @return ReleaseCleanupTask
     */
    public function setKeepAmount($keepAmount)
    {
        $this->keepAmount = $keepAmount;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            AccompliEvents::DEPLOY_RELEASE => array(
                array('onCleanupOldReleases', -255),
            ),
        );
    }

    /**
     * Constructs a new ReleaseCleanupTask.
     *
     * @param number $keepAmount
     *
     * @throws InvalidArgumentException
     */
    public function __construct($keepAmount)
    {
        if (!is_numeric($keepAmount)) {
            throw new InvalidArgumentException(sprintf('The given keepAmount %s is not a number.', $keepAmount));
        }
        $this->setKeepAmount($keepAmount);
    }

    /**
     * Clean up old release directories.
     *
     * @param WorkspaceEvent           $event
     * @param string                   $eventName
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function onCleanupOldReleases(DeployReleaseEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        if ($this->getKeepAmount() !== null) {
            $workspace = $event->getRelease()->getWorkspace();
            $host = $workspace->getHost();
            $connection = $this->ensureConnection($host);

            $directory = $workspace->getReleasesDirectory();
            $context = array('directory' => $directory, 'event.task.action' => TaskInterface::ACTION_IN_PROGRESS);
            $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::NOTICE, 'Cleaning up releases in directory "{directory}".', $eventName, $this, $context));

            $releases = $this->getReleasesToCleanUp($connection, $directory);
            foreach ($releases as $release) {
                $releaseDirectory = $directory.$release;
                if ($connection->isDirectory($releaseDirectory)) {
                    $result = $connection->delete($releaseDirectory, true);
                    if ($result) {
                        $context = array('release' => $release, 'event.task.action' => TaskInterface::ACTION_COMPLETED, 'directory' => $directory);
                        $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::NOTICE, 'Cleaned up release {release} in directory "{directory}".', $eventName, $this, $context));
                    }
                }
            }
            $context = array('directory' => $directory, 'event.task.action' => TaskInterface::ACTION_COMPLETED);
            $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::NOTICE, 'Cleaned up old releases in directory "{directory}".', $eventName, $this, $context));
        }
    }

    /**
     * Return a list of directories of releases to remove.
     *
     * @param ConnectionAdapterInterface $connection
     * @param string                     $directory
     *
     * @return array
     */
    private function getReleasesToCleanUp(ConnectionAdapterInterface $connection, $directory)
    {
        $releases = array();
        if ($connection->isDirectory($directory)) {
            $releases = $connection->getDirectoryContentsList($directory);
            usort($releases, 'version_compare');
            if (count($releases) - 1 > $this->getKeepAmount()) {
                $releases = array_slice($releases, 0, $this->getKeepAmount());
            }
        }

        return $releases;
    }
}
