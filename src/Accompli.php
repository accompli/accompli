<?php

namespace Accompli;

use Accompli\Deployment\Host;
use Accompli\Event\InstallReleaseEvent;
use Accompli\Event\PrepareReleaseEvent;
use Accompli\Event\PrepareWorkspaceEvent;
use Nijens\Utilities\ObjectFactory;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Accompli
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 * @package Accompli
 **/
class Accompli extends EventDispatcher
{
    /**
     * The Accompli CLI text logo
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
     * The Accompli CLI slogan text
     *
     * @var string
     **/
    const SLOGAN = "C'est fini. Accompli!";

    /**
     * The Accompli version
     *
     * @var string
     **/
    const VERSION = "0.1";

    /**
     * The configuration instance
     *
     * @access private
     * @var    ConfigurationInterface
     **/
    private $configuration;

    /**
     * __construct
     *
     * Constructs a new Accompli instance
     *
     * @access public
     * @param  ConfigurationInterface $configuration
     * @return null
     **/
    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * getConfiguration
     *
     * Returns the configuration instance
     *
     * @access public
     * @return ConfigurationInterface
     **/
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * initializeEventListeners
     *
     * Initializes the event listeners and subscribers configured in the configuration
     *
     * @access public
     * @return null
     **/
    public function initializeEventListeners()
    {
        $configuration = $this->getConfiguration();
        foreach ($configuration->getEventListeners() as $eventName => $listeners) {
            foreach ($listeners as $listener) {
                list($listenerClassName, $listenerMethodName) = explode('::', $listener);

                $listenerInstance = ObjectFactory::getInstance()->newInstance($listenerClassName);
                if ($listenerInstance !== null) {
                    $this->addListener($eventName, array($listenerInstance, $listenerMethodName) );
                }
            }
        }

        foreach ($configuration->getEventSubscribers() as $subscriber) {
            $subscriberInstance = ObjectFactory::getInstance()->newInstance($subscriber['class'], $subscriber);
            if ($subscriberInstance instanceof EventDispatcherInterface) {
                $this->addSubscriber($subscriberInstance);
            }
        }
    }

    /**
     * createRelease
     *
     * Dispatches release creation events
     *
     * @access public
     * @param  Host $host
     * @return null
     * @todo   Add DeploymentAdapter (connection)
     **/
    public function createRelease(Host $host)
    {
        $prepareWorkspaceEvent = new PrepareWorkspaceEvent($host);
        $this->dispatch(AccompliEvents::PREPARE_WORKSPACE, $prepareWorkspaceEvent);

        $prepareReleaseEvent = new PrepareReleaseEvent($prepareWorkspaceEvent->getWorkspace());
        $this->dispatch(AccompliEvents::PREPARE_RELEASE, $prepareReleaseEvent);

        $installReleaseEvent = new InstallReleaseEvent($prepareReleaseEvent->getRelease());
        $this->dispatch(AccompliEvents::INSTALL_RELEASE, $installReleaseEvent);
    }
}
