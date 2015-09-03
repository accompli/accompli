<?php

namespace Accompli\Test;

use Accompli\Accompli;
use PHPUnit_Framework_TestCase;

/**
 * AccompliTest
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 * @package Accompli\Test
 */
class AccompliTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests instantiation of Accompli
     *
     * @access public
     */
    public function testConstruct()
    {
        $configurationMock = $this->getMockBuilder('Accompli\\ConfigurationInterface')->getMock();

        new Accompli($configurationMock);
    }

    /**
     * Tests if Accompli::getConfiguration returns the expected result
     *
     * @access public
     */
    public function testGetConfiguration()
    {
        $configurationMock = $this->getMockBuilder('Accompli\\ConfigurationInterface')->getMock();

        $accompli = new Accompli($configurationMock);

        $this->assertInstanceOf("Accompli\\ConfigurationInterface", $accompli->getConfiguration());
        $this->assertSame($configurationMock, $accompli->getConfiguration());
    }
}
