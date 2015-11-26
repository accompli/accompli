<?php

namespace Accompli\Task;

use Accompli\AccompliEvents;
use Accompli\Chrono\Repository;
use Accompli\Deployment\Connection\ConnectionAdapterProcessExecutor;
use Accompli\Deployment\Release;
use Accompli\EventDispatcher\Event\LogEvent;
use Accompli\EventDispatcher\Event\PrepareReleaseEvent;
use Accompli\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LogLevel;
use RuntimeException;

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
     * @throws RuntimeException
     */
    public function onPrepareReleaseCheckoutRepository(PrepareReleaseEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        $context = array('repositoryUrl' => $this->repositoryUrl, 'version' => $event->getRelease()->getVersion(), 'done' => chr(251));

        $connection = $this->ensureConnection($event->getRelease()->getWorkspace()->getHost());
        $processExecutor = new ConnectionAdapterProcessExecutor($connection);

        $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, '[...] Creating checkout of repository "{repositoryUrl}" for version "{version}".', $event, $context));

        $repository = new Repository($this->repositoryUrl, $event->getRelease()->getPath(), $processExecutor);
        if ($repository->checkout($event->getRelease()->getVersion())) {
            $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, '[ {done} ] Creating checkout of repository "{repositoryUrl}" for version "{version}".', $event, $context));
        } else {
            throw new RuntimeException(sprintf('Checkout of repository "%s" for version "%s" failed.', $this->repositoryUrl, $event->getRelease()->getVersion()));
        }
    }
}
