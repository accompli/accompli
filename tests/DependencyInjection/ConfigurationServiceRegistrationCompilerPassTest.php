<?php

namespace Accompli\Test\DependencyInjection;

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
     * Tests if ConfigurationServiceRegistrationCompilerPass::process registers the configured deployment strategy as service.
     */
    public function testProcessAddsConfiguredDeploymentStrategyService()
    {
        $deploymentStrategyMock = $this->getMockBuilder('Accompli\Deployment\Strategy\DeploymentStrategyInterface')->getMock();

        $configurationMock = $this->getMockBuilder('Accompli\Configuration\ConfigurationInterface')->getMock();
        $configurationMock->expects($this->once())->method('getDeploymentStrategyClass')->willReturn(get_class($deploymentStrategyMock));

        $container = new ContainerBuilder();
        $container->set('configuration', $configurationMock);

        $compilerPass = new ConfigurationServiceRegistrationCompilerPass();
        $compilerPass->process($container);

        $this->assertTrue($container->has('deployment_strategy'));
    }

    /**
     * Tests if ConfigurationServiceRegistrationCompilerPass::process registers the configured connection adapters with the connection manager service.
     */
    public function testProcessAddsConfiguredConnectionClassesToConnectionManager()
    {
        $connectionMock = $this->getMockBuilder('Accompli\Deployment\Connection\ConnectionAdapterInterface')->getMock();
        $connectionMockClass = get_class($connectionMock);

        $configurationMock = $this->getMockBuilder('Accompli\Configuration\ConfigurationInterface')->getMock();
        $configurationMock->expects($this->once())->method('getDeploymentConnectionClasses')->willReturn(array('local' => $connectionMockClass));

        $connectionManagerMock = $this->getMockBuilder('Accompli\Deployment\Connection\ConnectionManagerInterface')->getMock();
        $connectionManagerMock->expects($this->once())
                ->method('registerConnectionAdapter')
                ->with($this->equalTo('local'), $this->equalTo($connectionMockClass));

        $container = new ContainerBuilder();
        $container->set('configuration', $configurationMock);
        $container->set('connection_manager', $connectionManagerMock);

        $compilerPass = new ConfigurationServiceRegistrationCompilerPass();
        $compilerPass->process($container);
    }

    /**
     * Tests if ConfigurationServiceRegistrationCompilerPass::process does nothing when no configuration service is registered.
     */
    public function testProcessDoesNothingWithoutRegisteredConfigurationService()
    {
        $connectionManagerMock = $this->getMockBuilder('Accompli\Deployment\Connection\ConnectionManagerInterface')->getMock();
        $connectionManagerMock->expects($this->never())->method('registerConnectionAdapter');

        $container = new ContainerBuilder();
        $container->set('connection_manager', $connectionManagerMock);

        $compilerPass = new ConfigurationServiceRegistrationCompilerPass();
        $compilerPass->process($container);

        $this->assertFalse($container->has('deployment_strategy'));
    }
}
