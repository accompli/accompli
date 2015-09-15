<?php

namespace Accompli\Test;

use Accompli\Accompli;
use PHPUnit_Framework_TestCase;

/**
 * AccompliTest.
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 */
class AccompliTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests instantiation of Accompli.
     */
    public function testConstruct()
    {
        $configurationMock = $this->getMockBuilder('Accompli\\ConfigurationInterface')->getMock();

        new Accompli($configurationMock);
    }

    /**
     * Tests if Accompli::getConfiguration returns the expected result.
     */
    public function testGetConfiguration()
    {
        $configurationMock = $this->getMockBuilder('Accompli\\ConfigurationInterface')->getMock();

        $accompli = new Accompli($configurationMock);

        $this->assertInstanceOf('Accompli\\ConfigurationInterface', $accompli->getConfiguration());
        $this->assertSame($configurationMock, $accompli->getConfiguration());
    }

    /**
     * Tests Accompli::getListeners returns the event listeners configured in the configuration after Accompli::initializeEventListeners.
     */
    public function testInitializeEventListeners()
    {
        $configurationMock = $this->getMockBuilder('Accompli\\ConfigurationInterface')->getMock();
        $configurationMock->expects($this->once())->method('getEventListeners')->willReturn(array('listener_event' => array('Accompli\\Test\\Mock\\EventListenerSubscriberMock::eventListener')));
        $configurationMock->expects($this->once())->method('getEventSubscribers')->willReturn(array(array('class' => 'Accompli\\Test\\Mock\\EventListenerSubscriberMock')));

        $accompli = new Accompli($configurationMock);
        $accompli->initializeEventListeners();

        $this->assertInternalType('array', $accompli->getListeners('listener_event'));
        $this->assertCount(1, $accompli->getListeners('listener_event'));
        $this->assertInternalType('array', $accompli->getListeners('subscribed_event'));
        $this->assertCount(1, $accompli->getListeners('subscribed_event'));
    }
}
