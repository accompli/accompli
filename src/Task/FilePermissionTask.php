<?php

namespace Accompli\Task;

use Accompli\AccompliEvents;
use Accompli\EventDispatcher\Event\InstallReleaseEvent;
use Accompli\EventDispatcher\Event\LogEvent;
use Accompli\EventDispatcher\EventDispatcherInterface;
use Accompli\Exception\TaskCommandExecutionException;
use GisoStallenberg\FilePermissionCalculator\FilePermissionCalculator;
use Psr\Log\LogLevel;

/**
 * FilePermissionTask.
 *
 * @author Deborah van der Vegt <deborah@connectholland.nl>
 */
class FilePermissionTask extends AbstractConnectedTask
{
    /**
     * The array with paths to set the permissions in.
     *
     * @var array
     */
    private $paths;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            AccompliEvents::INSTALL_RELEASE => array(
                array('onInstallReleaseUpdateFilePermissions', 0),
            ),
        );
    }

    /**
     * Constructs a new FilePermissionTask.
     *
     * @param array $paths
     */
    public function __construct($paths)
    {
        $this->paths = $paths;
    }

    /**
     * Sets the correct permissions and group for the configured path.
     *
     * @param InstallReleaseEvent      $event
     * @param string                   $eventName
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function onInstallReleaseUpdateFilePermissions(InstallReleaseEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        $host = $event->getRelease()->getWorkspace()->getHost();
        $connection = $this->ensureConnection($host);

        $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, 'Set correct permissions for configured paths...', $eventName, $this, array('event.task.action' => TaskInterface::ACTION_IN_PROGRESS)));

        if ($connection->isConnected() === true && $connection->isDirectory($event->getRelease()->getPath())) {
            foreach (array_keys($this->paths) as $configuredPath) {
                $path = $this->getPath($configuredPath, $event);
                $permissions = $this->getPermissionsForPath($configuredPath);
                $recursive = $this->getRecursive();

                $result = $connection->changePermissions($path, $permissions, $recursive);
            }

            if ($result === true) {
                $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, 'Permissions set for configured paths.', $eventName, $this, array('event.task.action' => TaskInterface::ACTION_COMPLETED, 'output.resetLine' => true)));
            } else {
                $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::WARNING, 'Failed setting the correct permissions for configured paths.', $eventName, $this, array('event.task.action' => TaskInterface::ACTION_FAILED)));
            }
        }
    }

    /**
     * Gets the directory for setting permissions.
     *
     * @param string              $configuredPath
     * @param InstallReleaseEvent $event
     *
     * @return string
     */
    protected function getPath($configuredPath, $event)
    {
        $releasePath = $event->getRelease()->getPath();

        return $releasePath.'/'.$configuredPath;
    }

    /**
     * Gets the calculated permissions based on the configuration.
     *
     * @param string $path
     *
     * @return int
     *
     * @throws TaskCommandExecutionException
     */
    protected function getPermissionsForPath($path)
    {
        if (array_key_exists('permissions', $this->paths[$path])) {
            $configuredPermissions = array_column($this->paths, 'permissions')[0];
            $permissions = FilePermissionCalculator::fromStringRepresentation(str_pad($configuredPermissions, 10, '-'))->getMode();

            return $permissions;
        } else {
            throw new TaskCommandExecutionException(sprintf('Failed to set permissions".', $this));
        }
    }

    /**
     * Set recursive value if configured.
     *
     * @return bool
     */
    protected function getRecursive()
    {
        if (array_key_exists('recursive', $this->paths)) {
            $configuredRecursive = array_column($this->paths, 'recursive')[0];

            return $configuredRecursive;
        } else {
            return false;
        }
    }
}
