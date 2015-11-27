<?php

namespace Accompli\Deployment\Strategy;

use Accompli\Configuration\ConfigurationInterface;
use Accompli\DependencyInjection\ConfigurationAwareInterface;
use Accompli\DependencyInjection\EventDispatcherAwareInterface;
use Accompli\EventDispatcher\EventDispatcherInterface;

/**
 * AbstractDeploymentStrategy.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
abstract class AbstractDeploymentStrategy implements DeploymentStrategyInterface, ConfigurationAwareInterface, EventDispatcherAwareInterface
{
    /**
     * The configuration instance.
     *
     * @var ConfigurationInterface
     */
    protected $configuration;

    /**
     * The event dispatcher instance.
     *
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * {@inheritdoc}
     */
    public function setConfiguration(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function deploy($version, $stage = null)
    {
    }
}
