<?php

namespace Accompli;

use Accompli\Deployment\Host;
use Accompli\Deployment\Release;
use Accompli\Deployment\Workspace;
use Accompli\Event\FailedEvent;
use Accompli\Event\InstallReleaseEvent;
use Accompli\Event\PrepareReleaseEvent;
use Accompli\Event\PrepareWorkspaceEvent;
use Nijens\Utilities\ObjectFactory;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Accompli.
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 **/
class Accompli extends EventDispatcher
{
    /**
     * The Accompli CLI text logo.
     *
     * @var string
     **/
    const LOGO = "
     _                                 _ _
    / \   ___ ___ ___  _ __ ___  _ __ | (_)
   / _ \ / __/ __/ _ \| '_ ` _ \| '_ \| | |
  / ___ \ (_| (_| (_) | | | | | | |_) | | |
 /_/   \_\___\___\___/|_| |_| |_| .__/|_|_|
 C'est fini. Accompli!          |_|

";

    /**
     * The Accompli CLI slogan text.
     *
     * @var string
     **/
    const SLOGAN = "C'est fini. Accompli!";

    /**
     * The Accompli version.
     *
     * @var string
     **/
    const VERSION = '0.1';

    /**
     * The configuration instance.
     *
     * @var ConfigurationInterface
     **/
    private $configuration;

    /**
     * Constructs a new Accompli instance.
     *
     * @param ConfigurationInterface $configuration
     */
    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Returns the configuration instance.
     *
     * @return ConfigurationInterface
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Initializes the event listeners and subscribers configured in the configuration.
     */
    public function initializeEventListeners()
    {
        $configuration = $this->getConfiguration();
        foreach ($configuration->getEventListeners() as $eventName => $listeners) {
            foreach ($listeners as $listener) {
                list($listenerClassName, $listenerMethodName) = explode('::', $listener);

                $listenerInstance = ObjectFactory::getInstance()->newInstance($listenerClassName);
                if ($listenerInstance !== null) {
                    $this->addListener($eventName, array($listenerInstance, $listenerMethodName));
                }
            }
        }

        foreach ($configuration->getEventSubscribers() as $subscriber) {
            $subscriberInstance = ObjectFactory::getInstance()->newInstance($subscriber['class'], $subscriber);
            if ($subscriberInstance instanceof EventSubscriberInterface) {
                $this->addSubscriber($subscriberInstance);
            }
        }
    }

    /**
     * Dispatches release creation events.
     *
     * @param Host $host
     *
     * @todo   Add DeploymentAdapter (connection)
     **/
    public function createRelease(Host $host)
    {
        $prepareWorkspaceEvent = new PrepareWorkspaceEvent($host);
        $this->dispatch(AccompliEvents::PREPARE_WORKSPACE, $prepareWorkspaceEvent);

        $workspace = $prepareWorkspaceEvent->getWorkspace();
        if ($workspace instanceof Workspace) {
            $prepareReleaseEvent = new PrepareReleaseEvent($workspace);
            $this->dispatch(AccompliEvents::PREPARE_RELEASE, $prepareReleaseEvent);

            $release = $prepareReleaseEvent->getRelease();
            if ($release instanceof Release) {
                $installReleaseEvent = new InstallReleaseEvent($release);
                $this->dispatch(AccompliEvents::INSTALL_RELEASE, $installReleaseEvent);

                return;
            }
        }

        $this->dispatch(AccompliEvents::CREATE_RELEASE_FAILED, new FailedEvent());
    }
}
