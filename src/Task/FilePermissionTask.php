<?php

namespace Accompli\Task;

use Accompli\AccompliEvents;
use Accompli\Deployment\Connection\ConnectionAdapterInterface;
use Accompli\EventDispatcher\Event\InstallReleaseEvent;
use Accompli\EventDispatcher\Event\LogEvent;
use Accompli\EventDispatcher\EventDispatcherInterface;
use Accompli\Exception\TaskRuntimeException;
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
     *
     * @throws TaskRuntimeException
     */
    public function onInstallReleaseUpdateFilePermissions(InstallReleaseEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        $host = $event->getRelease()->getWorkspace()->getHost();
        $connection = $this->ensureConnection($host);

        $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, 'Updating permissions for configured paths...', $eventName, $this, array('event.task.action' => TaskInterface::ACTION_IN_PROGRESS)));

        $releasePath = $event->getRelease()->getPath();

        $result = true;
        foreach ($this->paths as $path => $pathSettings) {
            $result = $result && $this->updateFilePermissions($connection, $releasePath, $path, $pathSettings);
        }

        if ($result === true) {
            $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, 'Updated permissions for configured paths.', $eventName, $this, array('event.task.action' => TaskInterface::ACTION_COMPLETED, 'output.resetLine' => true)));
        } else {
            throw new TaskRuntimeException('Failed updating the permissions for configured paths.', $this);
        }
    }

    /**
     * Update the file permissions per configured path.
     *
     * @param ConnectionAdapterInterface $connection
     * @param string                     $releasePath
     * @param string                     $path
     * @param string                     $pathSettings
     *
     * @return bool
     */
    private function updateFilePermissions(ConnectionAdapterInterface $connection, $releasePath, $path, $pathSettings)
    {
        $path = $releasePath.'/'.$path;

        if (isset($pathSettings['permissions']) === false) {
            return false;
        }

        $permissions = FilePermissionCalculator::fromStringRepresentation(str_pad($pathSettings['permissions'], 10, '-'))->getMode();

        $recursive = false;
        if (isset($pathSettings['recursive'])) {
            $recursive = $pathSettings['recursive'];
        }

        return $connection->changePermissions($path, $permissions, $recursive);
    }
}
