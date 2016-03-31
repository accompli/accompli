<?php

namespace Accompli\Test\DependencyInjection;

use Accompli\DependencyInjection\AwarenessCompilerPass;
use PHPUnit_Framework_TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * AwarenessCompilerPassTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class AwarenessCompilerPassTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if AwarenessCompilerPass::process adds a method call to the awareness setter with a reference to the service.
     */
    public function testProcess()
    {
        $configurationAwarenessMock = $this->getMockBuilder('Accompli\DependencyInjection\ConfigurationAwareInterface')->getMock();
        $configurationMock = $this->getMockBuilder('Accompli\Configuration\ConfigurationInterface')->getMock();

        $container = new ContainerBuilder();
        $container->set('configuration', $configurationMock);
        $container->setDefinition('awareness_mock', new Definition(get_class($configurationAwarenessMock)));

        $compilerPass = new AwarenessCompilerPass();
        $compilerPass->process($container);

        $methodCalls = $container->getDefinition('awareness_mock')->getMethodCalls();
        $this->assertCount(1, $methodCalls);
        $this->assertArraySubset(array('setConfiguration', array(new Reference('configuration'))), $methodCalls[0]);
    }
}
