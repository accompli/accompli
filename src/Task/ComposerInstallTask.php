<?php

namespace Accompli\Task;

use Accompli\AccompliEvents;
use Accompli\Deployment\Workspace;
use Accompli\EventDispatcher\Event\InstallReleaseEvent;
use Accompli\EventDispatcher\Event\LogEvent;
use Accompli\EventDispatcher\Event\WorkspaceEvent;
use Accompli\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LogLevel;
use RuntimeException;

/**
 * ComposerInstallTask.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class ComposerInstallTask extends AbstractConnectedTask
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            AccompliEvents::PREPARE_WORKSPACE => array(
                array('onPrepareWorkspaceInstallComposer', 0),
            ),
            AccompliEvents::INSTALL_RELEASE => array(
                array('onInstallReleaseExecuteComposerInstall', 0),
            ),
        );
    }

    /**
     * Installs or updates the Composer binary.
     *
     * @param WorkspaceEvent           $event
     * @param string                   $eventName
     * @param EventDispatcherInterface $eventDispatcher
     *
     * @throws RuntimeException
     */
    public function onPrepareWorkspaceInstallComposer(WorkspaceEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        $host = $event->getHost();
        $connection = $this->ensureConnection($host);

        $workspace = $event->getWorkspace();
        if ($workspace instanceof Workspace) {
            if ($connection->isFile($host->getPath().'/composer.phar') === false) {
                $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::DEBUG, 'Installing the Composer binary.', $eventName, $this, array('event.task.action' => TaskInterface::ACTION_IN_PROGRESS)));

                $connection->changeWorkingDirectory($host->getPath());
                $result = $connection->executeCommand('php -r "readfile(\'https://getcomposer.org/installer\');" | php');
                if ($result->isSuccessful()) {
                    $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::DEBUG, 'Installed the Composer binary.', $eventName, $this, array('event.task.action' => TaskInterface::ACTION_COMPLETED, 'output.resetLine' => true)));
                } else {
                    $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::WARNING, 'Failed installing the Composer binary.', $eventName, $this, array('event.task.action' => TaskInterface::ACTION_FAILED)));
                }
            } else {
                $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::DEBUG, 'Updating the Composer binary.', $eventName, $this, array('event.task.action' => TaskInterface::ACTION_IN_PROGRESS)));

                $connection->changeWorkingDirectory($host->getPath());
                $result = $connection->executeCommand('php composer.phar self-update');
                if ($result->isSuccessful()) {
                    $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::DEBUG, 'Updated the Composer binary.', $eventName, $this, array('event.task.action' => TaskInterface::ACTION_COMPLETED, 'output.resetLine' => true)));
                } else {
                    $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::WARNING, 'Failed updating the Composer binary.', $eventName, $this, array('event.task.action' => TaskInterface::ACTION_FAILED)));
                }
            }

            return;
        }

        throw new RuntimeException('The workspace of the host has not been created.');
    }

    /**
     * Runs the Composer install command to install the dependencies for the release.
     *
     * @param InstallReleaseEvent      $event
     * @param string                   $eventName
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function onInstallReleaseExecuteComposerInstall(InstallReleaseEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        $release = $event->getRelease();
        $host = $release->getWorkspace()->getHost();
        $connection = $this->ensureConnection($host);

        $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, 'Installing Composer dependencies.', $eventName, $this, array('event.task.action' => TaskInterface::ACTION_IN_PROGRESS)));

        $connection->changeWorkingDirectory($host->getPath());
        $result = $connection->executeCommand(sprintf('php composer.phar install --working-dir="%s" --no-dev --no-scripts --optimize-autoloader', $release->getPath()));
        if ($result->isSuccessful()) {
            $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, 'Installed Composer dependencies.', $eventName, $this, array('event.task.action' => TaskInterface::ACTION_COMPLETED, 'output.resetLine' => true)));
        } else {
            $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::CRITICAL, 'Failed installing Composer dependencies.', $eventName, $this, array('event.task.action' => TaskInterface::ACTION_FAILED, 'output.resetLine' => true)));
        }
    }
}
