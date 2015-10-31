<?php

namespace Accompli\Test;

use Accompli\DependencyInjection\ConfigurationServiceRegistrationCompilerPass;
use PHPUnit_Framework_TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * ConfigurationServiceRegistrationCompilerPassTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class ConfigurationServiceRegistrationCompilerPassTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if ConfigurationServiceRegistrationCompilerPass::process registers the configured deployment classes as service.
     */
    public function testProcess()
    {
        $deploymentStrategyMock = $this->getMockBuilder('Accompli\Deployment\Strategy\DeploymentStrategyInterface')->getMock();
        $connectionMock = $this->getMockBuilder('Accompli\Deployment\Connection\ConnectionAdapterInterface')->getMock();

        $configurationMock = $this->getMockBuilder('Accompli\Configuration\ConfigurationInterface')->getMock();
        $configurationMock->expects($this->once())->method('getDeploymentStrategyClass')->willReturn(get_class($deploymentStrategyMock));
        $configurationMock->expects($this->once())->method('getDeploymentConnectionClasses')->willReturn(array('local' => get_class($connectionMock)));

        $container = new ContainerBuilder();
        $container->set('configuration', $configurationMock);

        $compilerPass = new ConfigurationServiceRegistrationCompilerPass();
        $compilerPass->process($container);

        $this->assertTrue($container->has('deployment_strategy'));
        $this->assertTrue($container->has('local_connection'));
    }

    /**
     * Tests if ConfigurationServiceRegistrationCompilerPass::process does nothing when no configuration service is registered.
     */
    public function testProcessDoesNothingWithoutRegisteredConfigurationService()
    {
        $container = new ContainerBuilder();

        $compilerPass = new ConfigurationServiceRegistrationCompilerPass();
        $compilerPass->process($container);

        $this->assertFalse($container->has('deployment_strategy'));
        $this->assertFalse($container->has('local_connection'));
    }
}
