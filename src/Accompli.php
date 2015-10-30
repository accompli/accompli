<?php

namespace Accompli;

use Accompli\Configuration\ConfigurationInterface;
use Accompli\DependencyInjection\AwarenessCompilerPass;
use Accompli\Deployment\Host;
use Accompli\Deployment\Release;
use Accompli\Deployment\Workspace;
use Accompli\Event\FailedEvent;
use Accompli\Event\InstallReleaseEvent;
use Accompli\Event\PrepareReleaseEvent;
use Accompli\Event\PrepareWorkspaceEvent;
use Nijens\Utilities\ObjectFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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
     * @var ParameterBagInterface
     */
    private $parameters;

    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * Constructs a new Accompli instance.
     *
     * @param ParameterBagInterface $parameters
     */
    public function __construct(ParameterBagInterface $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Initializes Accompli.
     */
    public function initialize()
    {
        $this->initializeContainer();
        $this->initializeEventListeners();
    }

    /**
     * Initializes the service container.
     */
    public function initializeContainer()
    {
        $this->container = $this->buildContainer();
        $this->container->compile();
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
     * Returns the service container.
     *
     * @return ContainerBuilder
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Returns the configuration instance.
     *
     * @return ConfigurationInterface
     */
    public function getConfiguration()
    {
        return $this->container->get('configuration');
    }

    /**
     * Dispatches release installation events.
     *
     * @param Host $host
     *
     * @todo   Add DeploymentAdapter (connection)
     **/
    public function installRelease(Host $host)
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

        $this->dispatch(AccompliEvents::INSTALL_RELEASE_FAILED, new FailedEvent());
    }

    /**
     * Builds the service container.
     *
     * @return ContainerBuilder
     */
    protected function buildContainer()
    {
        $container = new ContainerBuilder($this->parameters);
        $container->addCompilerPass(new AwarenessCompilerPass());

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/Resources'));
        $loader->load('services.yml');

        return $container;
    }
}
