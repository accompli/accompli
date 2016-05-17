<?php

namespace Accompli\Task;

use Accompli\AccompliEvents;
use Accompli\Chrono\Repository;
use Accompli\Deployment\Connection\ConnectionAdapterProcessExecutor;
use Accompli\Deployment\Release;
use Accompli\EventDispatcher\Event\LogEvent;
use Accompli\EventDispatcher\Event\PrepareReleaseEvent;
use Accompli\EventDispatcher\EventDispatcherInterface;
use Accompli\Exception\TaskCommandExecutionException;
use Psr\Log\LogLevel;

/**
 * RepositoryCheckoutTask.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class RepositoryCheckoutTask extends AbstractConnectedTask
{
    /**
     * The URL of the repository.
     *
     * @var string
     */
    private $repositoryUrl;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            AccompliEvents::PREPARE_RELEASE => array(
                array('onPrepareReleaseConstructReleaseInstance', 10),
                array('onPrepareReleaseCheckoutRepository', 0),
            ),
        );
    }

    /**
     * Constructs a new RepositoryCheckoutTask.
     *
     * @param string $repositoryUrl
     */
    public function __construct($repositoryUrl)
    {
        $this->repositoryUrl = $repositoryUrl;
    }

    /**
     * Creates a Release instance when not already available.
     *
     * @param PrepareReleaseEvent $event
     */
    public function onPrepareReleaseConstructReleaseInstance(PrepareReleaseEvent $event)
    {
        if (($event->getRelease() instanceof Release) === false) {
            $release = new Release($event->getVersion());

            $event->getWorkspace()->addRelease($release);
            $event->setRelease($release);
        }
    }

    /**
     * Creates a checkout of the repository for the release.
     *
     * @param PrepareReleaseEvent      $event
     * @param string                   $eventName
     * @param EventDispatcherInterface $eventDispatcher
     *
     * @throws TaskRuntimeException when the checkout of the repository has failed.
     */
    public function onPrepareReleaseCheckoutRepository(PrepareReleaseEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        $context = array('repositoryUrl' => $this->repositoryUrl, 'version' => $event->getRelease()->getVersion(), 'event.task.action' => TaskInterface::ACTION_IN_PROGRESS);

        $connection = $this->ensureConnection($event->getRelease()->getWorkspace()->getHost());
        $processExecutor = new ConnectionAdapterProcessExecutor($connection);

        $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::NOTICE, 'Creating checkout of repository "{repositoryUrl}" for version "{version}".', $eventName, $this, $context));

        $repository = new Repository($this->repositoryUrl, $event->getRelease()->getPath(), $processExecutor);
        if ($repository->checkout($event->getRelease()->getVersion())) {
            $context['event.task.action'] = TaskInterface::ACTION_COMPLETED;
            $context['output.resetLine'] = true;

            $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::NOTICE, 'Created checkout of repository "{repositoryUrl}" for version "{version}".', $eventName, $this, $context));
        } else {
            throw new TaskCommandExecutionException(sprintf('Failed to checkout version "%s" from repository "%s".', $event->getRelease()->getVersion(), $this->repositoryUrl), $processExecutor->getLastProcessExecutionResult(), $this);
        }
    }
}
