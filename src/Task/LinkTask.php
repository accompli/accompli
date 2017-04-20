<?php

namespace Accompli\Task;

use Accompli\AccompliEvents;
use Accompli\Deployment\Release;
use Accompli\EventDispatcher\Event\InstallReleaseEvent;
use Accompli\EventDispatcher\Event\LogEvent;
use Accompli\EventDispatcher\EventDispatcherInterface;
use Accompli\Exception\TaskRuntimeException;
use Psr\Log\LogLevel;

/**
 * LinkTask.
 *
 * @author Reyo Stallenberg <reyo@connectholland.nl>
 */
class LinkTask extends AbstractConnectedTask
{
    /**
     * The array with paths to link.
     *
     * @var array
     */
    private $links;

    /**
     * The array with environment variables to use in the paths.
     *
     * @var array
     */
    private $environmentVariables;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            AccompliEvents::INSTALL_RELEASE => array(
                array('onInstallReleaseCreateLinks', 0),
            ),
        );
    }

    /**
     * Constructs a new LinkTask.
     *
     * @param array $links
     */
    public function __construct(array $links)
    {
        $this->links = $links;
    }

    /**
     * Creates the configured links.
     *
     * @param InstallReleaseEvent      $event
     * @param string                   $eventName
     * @param EventDispatcherInterface $eventDispatcher
     *
     * @throws TaskRuntimeException
     */
    public function onInstallReleaseCreateLinks(InstallReleaseEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        $release = $event->getRelease();
        $host = $release->getWorkspace()->getHost();
        $connection = $this->ensureConnection($host);

        $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::NOTICE, 'Linking paths...', $eventName, $this, array('event.task.action' => TaskInterface::ACTION_IN_PROGRESS)));

        $this->gatherEnvironmentVariables($release);

        $releasePath = $release->getPath();
        foreach ($this->links as $target => $source) {
            $source = strtr($source, $this->environmentVariables);

            $targetPath = $releasePath.'/'.$target;
            $sourcePath = $releasePath.'/'.$source;

            $context = array('target' => $target, 'source' => $source, 'event.task.action' => TaskInterface::ACTION_IN_PROGRESS);

            $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, 'Creating link in "{target}" to "{source}".', $eventName, $this, $context));

            if ($connection->isFile($sourcePath) === false && $connection->isDirectory($sourcePath) === false && $connection->isLink($sourcePath) === false) {
                $context['event.task.action'] = TaskInterface::ACTION_FAILED;
                $context['output.resetLine'] = true;

                $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::WARNING, 'Failed creating link for "{target}" because "{source}" does not exist.', $eventName, $this, $context));

                throw new TaskRuntimeException('Failed linking paths.', $this);
            }

            if ($connection->isLink($targetPath) === false || $connection->readLink($targetPath) !== $sourcePath) {
                $connection->delete($targetPath);

                if ($connection->link($sourcePath, $targetPath)) {
                    $context['event.task.action'] = TaskInterface::ACTION_COMPLETED;
                    $context['output.resetLine'] = true;

                    $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, 'Created link in "{target}" to "{source}".', $eventName, $this, $context));

                    continue;
                } else {
                    $context['event.task.action'] = TaskInterface::ACTION_FAILED;
                    $context['output.resetLine'] = true;

                    $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::WARNING, 'Failed creating link for "{target}".', $eventName, $this, $context));
                }

                throw new TaskRuntimeException('Failed linking paths.', $this);
            }
        }

        $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::NOTICE, 'Linked paths.', $eventName, $this, array('event.task.action' => TaskInterface::ACTION_COMPLETED, 'output.resetLine' => true)));
    }

    /**
     * Gathers environment variables to use in the paths.
     *
     * @param Release $release
     */
    private function gatherEnvironmentVariables(Release $release)
    {
        $this->environmentVariables = array(
            '%data%' => $release->getWorkspace()->getDataDirectory(),
            '%stage%' => $release->getWorkspace()->getHost()->getStage(),
            '%version%' => $release->getVersion(),
        );
    }
}
