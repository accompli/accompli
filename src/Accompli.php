<?php

namespace Accompli;

use Accompli\Configuration\ConfigurationInterface;
use Accompli\DependencyInjection\AwarenessCompilerPass;
use Accompli\DependencyInjection\ConfigurationServiceRegistrationCompilerPass;
use Nijens\ProtocolStream\Stream\Stream;
use Nijens\ProtocolStream\StreamManager;
use Nijens\Utilities\ObjectFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Accompli.
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 **/
class Accompli
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
    const VERSION = '0.3.1';

    /**
     * The parameter bag instance containing parameters for the service container.
     *
     * @var ParameterBagInterface
     */
    private $parameters;

    /**
     * The service container instance.
     *
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
        $this->initializeStreamWrapper();
        $this->initializeContainer();
        $this->initializeEventListeners();

        $this->dispatch(AccompliEvents::INITIALIZE);
    }

    /**
     * Initializes the stream wrapper to load recipes within the Accompli package.
     */
    public function initializeStreamWrapper()
    {
        $stream = new Stream('accompli', array(
                'recipe' => realpath(__DIR__.'/Resources/recipe'),
            ), false);

        StreamManager::create()->registerStream($stream);
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
        $eventDispatcher = $this->getContainer()->get('event_dispatcher');
        foreach ($configuration->getEventListeners() as $eventName => $listeners) {
            foreach ($listeners as $listener) {
                list($listenerClassName, $listenerMethodName) = explode('::', $listener);

                $listenerInstance = ObjectFactory::getInstance()->newInstance($listenerClassName);
                if ($listenerInstance !== null) {
                    $eventDispatcher->addListener($eventName, array($listenerInstance, $listenerMethodName));
                }
            }
        }

        foreach ($configuration->getEventSubscribers() as $subscriber) {
            $subscriberInstance = ObjectFactory::getInstance()->newInstance($subscriber['class'], $subscriber);
            if ($subscriberInstance instanceof EventSubscriberInterface) {
                $eventDispatcher->addSubscriber($subscriberInstance);
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
        return $this->getContainer()->get('configuration');
    }

    /**
     * Triggers the install on the configured deployment strategy.
     *
     * @param string      $version
     * @param string|null $stage
     *
     * @return bool
     */
    public function install($version, $stage = null)
    {
        $deploymentStrategy = $this->getContainer()->get('deployment_strategy');

        $result = $deploymentStrategy->install($version, $stage);

        $this->dispatch(AccompliEvents::INSTALL_COMMAND_COMPLETE);

        return $result;
    }

    /**
     * Triggers the deployment on the configured deployment strategy.
     *
     * @param string $version
     * @param string $stage
     *
     * @return bool
     */
    public function deploy($version, $stage)
    {
        $deploymentStrategy = $this->getContainer()->get('deployment_strategy');

        $result = $deploymentStrategy->deploy($version, $stage);

        $this->dispatch(AccompliEvents::DEPLOY_COMMAND_COMPLETE);

        return $result;
    }

    /**
     * Builds the service container.
     *
     * @return ContainerBuilder
     */
    protected function buildContainer()
    {
        $container = new ContainerBuilder($this->parameters);
        $container->addCompilerPass(new ConfigurationServiceRegistrationCompilerPass());
        $container->addCompilerPass(new AwarenessCompilerPass());

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/Resources'));
        $loader->load('services.yml');

        return $container;
    }

    /**
     * Dispatches an event to all registered listeners.
     *
     * @param string $eventName
     */
    private function dispatch($eventName)
    {
        $this->getContainer()->get('event_dispatcher')->dispatch($eventName);
    }
}
