<?php

namespace Accompli\DependencyInjection;

use Accompli\Configuration\ConfigurationInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * ConfigurationServiceRegistrationCompilerPass.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class ConfigurationServiceRegistrationCompilerPass implements CompilerPassInterface
{
    /**
     * Adds deployment classes, defined in the configuration service, as services.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $configuration = $container->get('configuration', ContainerInterface::NULL_ON_INVALID_REFERENCE);
        if ($configuration instanceof ConfigurationInterface) {
            $this->registerService($container, 'deployment_strategy', $configuration->getDeploymentStrategyClass());

            foreach ($configuration->getDeploymentConnectionClasses() as $connectionId => $connectionClass) {
                $this->registerService($container, $connectionId.'_connection', $connectionClass);
            }
        }
    }

    /**
     * Registers a service with the service container when the class exists.
     *
     * @param ContainerBuilder $container
     * @param string           $serviceId
     * @param string           $serviceClass
     */
    private function registerService(ContainerBuilder $container, $serviceId, $serviceClass)
    {
        if (class_exists($serviceClass)) {
            $container->register($serviceId, $serviceClass);
        }
    }
}
