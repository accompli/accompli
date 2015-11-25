<?php

namespace Accompli\Task;

use Accompli\AccompliEvents;
use Accompli\Deployment\Workspace;
use Accompli\EventDispatcher\Event\LogEvent;
use Accompli\EventDispatcher\Event\PrepareWorkspaceEvent;
use Accompli\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LogLevel;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcher;

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
     * @param PrepareWorkspaceEvent $event
     * @param string                $eventName
     * @param EventDispatcher       $eventDispatcher
     */
    public function onPrepareWorkspaceConstructWorkspaceInstance(PrepareWorkspaceEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, '[...] Creating Workspace.', $event));

        $workspace = new Workspace($event->getHost());
        $workspace->setReleasesDirectory($this->releasesDirectory);
        $workspace->setDataDirectory($this->dataDirectory);
        $workspace->setCacheDirectory($this->cacheDirectory);
        $workspace->setOtherDirectories($this->otherDirectories);

        $event->setWorkspace($workspace);

        $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, '[ {done} ] Created Workspace.', $event, array('done' => chr(251))));
    }

    /**
     * Creates the workspace directories when not already existing.
     *
     * @param PrepareWorkspaceEvent $event
     * @param string                $eventName
     * @param EventDispatcher       $eventDispatcher
     *
     * @throws RuntimeException
     */
    public function onPrepareWorkspaceCreateWorkspace(PrepareWorkspaceEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        $workspace = $event->getWorkspace();
        $connection = $this->ensureConnection($workspace->getHost());

        $workspacePath = $workspace->getHost()->getPath();
        if ($connection->isDirectory($workspacePath) === false && $connection->createDirectory($workspacePath) === false) {
            throw new RuntimeException(sprintf('The workspace path "%s" does not exist and could not be created.', $workspacePath));
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
            $context = array('directory' => $directory, 'done' => chr(251));

            $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, '[...] Creating directory "{directory}".', $event, $context));
            if ($connection->isDirectory($directory) === false) {
                if ($connection->createDirectory($directory)) {
                    $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, '[ {done} ] Created directory "{directory}".', $event, $context));
                } else {
                    $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::WARNING, '[ X ] Failed creating directory "{directory}".', $event, $context));
                }
            } else {
                $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, '[ {done} ] Directory "{directory}" exists.', $event, $context));
            }
        }
    }
}
