<?php

namespace Accompli\Test;

use PHPUnit_Framework_TestCase;

/**
 * AbstractDeploymentStrategyTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class AbstractDeploymentStrategyTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if AbstractDeploymentStrategy::setConfiguration sets the configuration property of the class.
     */
    public function testSetConfiguration()
    {
        $configurationMock = $this->getMockBuilder('Accompli\Configuration\ConfigurationInterface')->getMock();
        $deploymentStrategyMock = $this->getMockBuilder('Accompli\Deployment\Strategy\AbstractDeploymentStrategy')->getMockForAbstractClass();

        $deploymentStrategyMock->setConfiguration($configurationMock);

        $this->assertAttributeSame($configurationMock, 'configuration', $deploymentStrategyMock);
    }

    /**
     * Tests if AbstractDeploymentStrategy::setEventDispatcher sets the event dispatcher property of the class.
     */
    public function testSetEventDispatcher()
    {
        $eventDispatcherMock = $this->getMockBuilder('Accompli\EventDispatcher\EventDispatcherInterface')->getMock();
        $deploymentStrategyMock = $this->getMockBuilder('Accompli\Deployment\Strategy\AbstractDeploymentStrategy')->getMockForAbstractClass();

        $deploymentStrategyMock->setEventDispatcher($eventDispatcherMock);

        $this->assertAttributeSame($eventDispatcherMock, 'eventDispatcher', $deploymentStrategyMock);
    }
}
