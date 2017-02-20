<?php

namespace Accompli\Task;

use Accompli\AccompliEvents;
use Accompli\EventDispatcher\Event\LogEvent;
use Accompli\EventDispatcher\Event\PrepareReleaseEvent;
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
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            AccompliEvents::PREPARE_RELEASE => array(
                array('onPrepareReleaseCreateLinks', 0),
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
     * @param PrepareReleaseEvent      $event
     * @param string                   $eventName
     * @param EventDispatcherInterface $eventDispatcher
     *
     * @throws TaskRuntimeException
     */
    public function onPrepareReleaseCreateLinks(PrepareReleaseEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        $eventDispatcher->dispatch(
            AccompliEvents::LOG,
            new LogEvent(
                LogLevel::NOTICE,
                'Linking data directories for the configured paths...',
                $eventName,
                $this,
                array(
                    'event.task.action' => TaskInterface::ACTION_IN_PROGRESS,
                )
            )
        );

        $release = $event->getRelease();
        $workspace = $event->getWorkspace();
        $host = $workspace->getHost();
        $connection = $this->ensureConnection($host);

        $releasePath = $release->getPath();
        $dataDirectory = $workspace->getDataDirectory();

        $result = true;
        foreach ($this->links as $link => $target) {
            $fullTarget = sprintf('%s/%s/%s', $dataDirectory, $host->getStage(), $target);
            $fullLink = sprintf('%s/%s', $releasePath, $link);

            if (!$connection->isDirectory($fullTarget)) {
                $connection->createDirectory($fullTarget, 0777, true);
            }
            if ($connection->readLink($fullLink) != $fullTarget) {
                if ($connection->isLink($fullLink)) {
                    $connection->delete($fullLink);
                }
                $result = $result && $connection->link($fullTarget, $fullLink);
            }
        }

        if ($result === true) {
            $eventDispatcher->dispatch(
                AccompliEvents::LOG,
                new LogEvent(
                    LogLevel::NOTICE,
                    'Linked data directories for the configured paths.',
                    $eventName,
                    $this,
                    array(
                        'event.task.action' => TaskInterface::ACTION_COMPLETED,
                        'output.resetLine' => true,
                    )
                )
            );
        } else {
            throw new TaskRuntimeException('Failed linking data directories for the configured paths.', $this);
        }
    }
}
