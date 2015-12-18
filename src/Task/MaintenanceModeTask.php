<?php

namespace Accompli\Task;

use Accompli\AccompliEvents;
use Accompli\EventDispatcher\Event\LogEvent;
use Accompli\EventDispatcher\Event\PrepareDeployReleaseEvent;
use Accompli\EventDispatcher\Event\WorkspaceEvent;
use Accompli\EventDispatcher\EventDispatcherInterface;
use Accompli\Utility\VersionCategoryComparator;
use InvalidArgumentException;
use Psr\Log\LogLevel;
use RuntimeException;

/**
 * MaintenanceModeTask.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class MaintenanceModeTask extends AbstractConnectedTask
{
    /**
     * The maintenance page strategy.
     *
     * @var string
     */
    private $strategy;

    /**
     * The local path to the directory containing the maintenance page.
     *
     * @var string
     */
    private $localMaintenanceDirectory;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            AccompliEvents::PREPARE_WORKSPACE => array(
                array('onPrepareWorkspaceUploadMaintenancePage', 0),
            ),
            AccompliEvents::PREPARE_DEPLOY_RELEASE => array(
                array('onPrepareDeployReleaseLinkMaintenancePageToStage', 0),
            ),
        );
    }

    /**
     * Constructs a new MaintenanceTask.
     *
     * @param string $strategy
     *
     * @throws InvalidArgumentException
     */
    public function __construct($strategy = VersionCategoryComparator::MATCH_MAJOR_DIFFERENCE)
    {
        if (in_array($strategy, array(VersionCategoryComparator::MATCH_MAJOR_DIFFERENCE, VersionCategoryComparator::MATCH_MINOR_DIFFERENCE, VersionCategoryComparator::MATCH_ALWAYS)) === false) {
            throw new InvalidArgumentException(sprintf('The strategy type "%s" is invalid.', $strategy));
        }

        $this->strategy = $strategy;
        $this->localMaintenanceDirectory = realpath(__DIR__.'/../Resources/maintenance');
    }

    /**
     * Uploads the maintenance page (and related resources).
     *
     * @param WorkspaceEvent           $event
     * @param string                   $eventName
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function onPrepareWorkspaceUploadMaintenancePage(WorkspaceEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        $host = $event->getWorkspace()->getHost();
        $connection = $this->ensureConnection($host);

        $directory = $host->getPath().'/maintenance/';
        $context = array('directory' => $directory, 'event.task.action' => TaskInterface::ACTION_IN_PROGRESS);

        $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, 'Creating directory "{directory}".', $eventName, $this, $context));
        if ($connection->isDirectory($directory) === false) {
            if ($connection->createDirectory($directory)) {
                $context['event.task.action'] = TaskInterface::ACTION_COMPLETED;
                $context['output.resetLine'] = true;

                $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, 'Created directory "{directory}".', $eventName, $this, $context));
            } else {
                $context['event.task.action'] = TaskInterface::ACTION_FAILED;
                $context['output.resetLine'] = true;

                $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::WARNING, 'Failed creating directory "{directory}".', $eventName, $this, $context));
            }
        } else {
            $context['event.task.action'] = TaskInterface::ACTION_COMPLETED;
            $context['output.resetLine'] = true;

            $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, 'Directory "{directory}" exists.', $eventName, $this, $context));
        }

        if ($connection->isDirectory($directory)) {
            $files = array_diff(scandir($this->localMaintenanceDirectory), array('.', '..'));
            foreach ($files as $file) {
                $localFile = $this->localMaintenanceDirectory.'/'.$file;
                if (is_file($localFile)) {
                    $context = array('file' => $localFile, 'event.task.action' => TaskInterface::ACTION_COMPLETED);

                    $uploaded = $connection->putFile($localFile, $host->getPath().'maintenance/'.$file);
                    if ($uploaded === true) {
                        $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::DEBUG, 'Uploaded file "{file}".', $eventName, $this, $context));
                    }
                }
            }
        }
    }

    /**
     * Links the maintenance page to the stage being deployed.
     *
     * @param PrepareDeployReleaseEvent $event
     * @param string                    $eventName
     * @param EventDispatcherInterface  $eventDispatcher
     *
     * @throws RuntimeException when not able to link the maintenance page.
     */
    public function onPrepareDeployReleaseLinkMaintenancePageToStage(PrepareDeployReleaseEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        if (VersionCategoryComparator::matchesStrategy($this->strategy, $event->getRelease(), $event->getCurrentRelease()) === false) {
            $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::DEBUG, 'Skipped linking maintenance page according to strategy.', $eventName, $this));

            return;
        }

        $host = $event->getWorkspace()->getHost();
        $connection = $this->ensureConnection($host);

        $linkSource = $host->getPath().'/maintenance/';
        $linkTarget = $host->getPath().'/'.$host->getStage();

        $context = array('linkTarget' => $linkTarget);

        if ($connection->isLink($linkTarget) && $connection->delete($linkTarget, false) === false) {
            $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::WARNING, 'Failed to remove existing "{linkTarget}" link.', $eventName, $this, $context));
        }

        $context['event.task.action'] = TaskInterface::ACTION_IN_PROGRESS;
        $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, 'Linking "{linkTarget}" to maintenance page.', $eventName, $this, $context));

        if ($connection->link($linkSource, $linkTarget)) {
            $context['event.task.action'] = TaskInterface::ACTION_COMPLETED;
            $context['output.resetLine'] = true;

            $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, 'Linked "{linkTarget}" to maintenance page.', $eventName, $this, $context));
        } else {
            $context['event.task.action'] = TaskInterface::ACTION_FAILED;
            $context['output.resetLine'] = true;

            $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, 'Linking "{linkTarget}" to maintenance page failed.', $eventName, $this, $context));

            throw new RuntimeException(sprintf('Linking "%s" to maintenance page failed.', $context['linkTarget']));
        }
    }
}
