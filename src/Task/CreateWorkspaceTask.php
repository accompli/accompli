<?php

namespace Accompli\Task;

use Accompli\AccompliEvents;
use Accompli\Deployment\Workspace;
use Accompli\EventDispatcher\Event\LogEvent;
use Accompli\EventDispatcher\Event\WorkspaceEvent;
use Accompli\EventDispatcher\EventDispatcherInterface;
use Accompli\Exception\TaskRuntimeException;
use Psr\Log\LogLevel;

/**
 * CreateWorkspaceTask.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class CreateWorkspaceTask extends AbstractConnectedTask
{
    /**
     * The relative path to the directory containing the releases.
     *
     * @var string
     */
    private $releasesDirectory;

    /**
     * The relative path to the directory containing user data.
     *
     * @var string
     */
    private $dataDirectory;

    /**
     * The relative path to the directory containing cache data.
     *
     * @var string
     */
    private $cacheDirectory;

    /**
     * The relative paths to other directories.
     *
     * @var array
     */
    private $otherDirectories;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            AccompliEvents::PREPARE_WORKSPACE => array(
                array('onPrepareWorkspaceConstructWorkspaceInstance', 10),
                array('onPrepareWorkspaceCreateWorkspace', 0),
            ),
            AccompliEvents::GET_WORKSPACE => array(
                array('onPrepareWorkspaceConstructWorkspaceInstance', 10),
            ),
        );
    }

    /**
     * Constructs a new CreateWorkspaceTask.
     *
     * @param string $releasesDirectory
     * @param string $dataDirectory
     * @param string $cacheDirectory
     * @param array  $otherDirectories
     */
    public function __construct($releasesDirectory = 'releases/', $dataDirectory = 'data/', $cacheDirectory = 'cache/', $otherDirectories = array())
    {
        $this->releasesDirectory = $releasesDirectory;
        $this->dataDirectory = $dataDirectory;
        $this->cacheDirectory = $cacheDirectory;
        $this->otherDirectories = $otherDirectories;
    }

    /**
     * Constructs a new Workspace instance and sets the Workspace on the event.
     *
     * @param WorkspaceEvent           $event
     * @param string                   $eventName
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function onPrepareWorkspaceConstructWorkspaceInstance(WorkspaceEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::NOTICE, 'Creating Workspace.', $eventName, $this, array('event.task.action' => TaskInterface::ACTION_IN_PROGRESS)));

        $workspace = new Workspace($event->getHost());
        $workspace->setReleasesDirectory($this->releasesDirectory);
        $workspace->setDataDirectory($this->dataDirectory);
        $workspace->setCacheDirectory($this->cacheDirectory);
        $workspace->setOtherDirectories($this->otherDirectories);

        $event->setWorkspace($workspace);

        $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::NOTICE, 'Created Workspace.', $eventName, $this, array('event.task.action' => TaskInterface::ACTION_COMPLETED, 'output.resetLine' => true)));
    }

    /**
     * Creates the workspace directories when not already existing.
     *
     * @param WorkspaceEvent           $event
     * @param string                   $eventName
     * @param EventDispatcherInterface $eventDispatcher
     *
     * @throws TaskRuntimeException when the workspace path doesn't exist and can't be created.
     */
    public function onPrepareWorkspaceCreateWorkspace(WorkspaceEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        $workspace = $event->getWorkspace();
        $connection = $this->ensureConnection($workspace->getHost());

        $workspacePath = $workspace->getHost()->getPath();
        if ($connection->isDirectory($workspacePath) === false && $connection->createDirectory($workspacePath) === false) {
            throw new TaskRuntimeException(sprintf('The workspace path "%s" does not exist and could not be created.', $workspacePath), $this);
        }

        $directories = array_merge(
            array(
                $workspace->getReleasesDirectory(),
                $workspace->getDataDirectory(),
                $workspace->getCacheDirectory(),
            ),
            $workspace->getOtherDirectories()
        );

        foreach ($directories as $directory) {
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
        }
    }
}
