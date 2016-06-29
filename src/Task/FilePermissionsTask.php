<?php

namespace Accompli\Task;

use Accompli\AccompliEvents;
use Accompli\EventDispatcher\Event\InstallReleaseEvent;
use Accompli\EventDispatcher\Event\LogEvent;
use Accompli\EventDispatcher\EventDispatcherInterface;
use Accompli\Exception\TaskCommandExecutionException;
use Psr\Log\LogLevel;

/**
 * SetRightsTask.
 *
 * @author Deborah van der Vegt <deborah@connectholland.nl>
 */
class FilePermissionsTask extends AbstractConnectedTask
{
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
     *
     */
    public function onInstallReleaseUpdateFilePermissions(InstallReleaseEvent $event, $eventName, EventDispatcherInterface $eventDispatcher) {
        $release = $event->getRelease();
        $host = $release->getWorkspace()->getHost();
        $connection = $this->ensureConnection($host);

        $releasePath = $release->getPath();

        if ($connection->isConnected() === true && $connection->isDirectory($releasePath)) {
            $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::NOTICE, 'Setting permissions for release directory.', $eventName, $this, array('event.task.action' => TaskInterface::ACTION_IN_PROGRESS)));

            $result = $connection->changePermissions($releasePath, 0770);

            if ($result === true) {
                $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::NOTICE, 'Set correct permissions for release directory.', $eventName, $this, array('event.task.action' => TaskInterface::ACTION_COMPLETED, 'output.resetLine' => true)));
            } else {
//                throw new TaskCommandExecutionException('Failed setting permissions for release directory', $this);
            }
        }
    }
}
