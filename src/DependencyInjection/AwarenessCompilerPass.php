<?php

namespace Accompli\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * AwarenessCompilerPass.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class AwarenessCompilerPass implements CompilerPassInterface
{
    /**
     * Processes awareness interfaces by adding a setter method call to the service definition with a reference to the service.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container) {
        foreach ($container->getDefinitions() as $definition) {
            $interfaces = class_implements($definition->getClass());
            foreach ($interfaces as $interface) {
                if (substr($interface, -14) === 'AwareInterface') {
                    $camelCasedServiceId = substr($interface, strrpos($interface, '\\') + 1, -14);
                    $serviceId = Container::underscore($camelCasedServiceId);

                    if ($container->has($serviceId)) {
                        $setterMethod = 'set'.$camelCasedServiceId;

                        $definition->addMethodCall($setterMethod, array(new Reference($serviceId)));
                    }
                }
            }
        }
    }
}
