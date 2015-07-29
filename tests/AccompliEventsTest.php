<?php

namespace Accompli\Test;

use Accompli\AccompliEvents;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * AccompliEventsTest
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 * @package Accompli\Test
 */
class AccompliEventsTest extends PHPUnit_Framework_TestCase
{
    /**
     * testLoadWithValidJSON
     *
     * @access public
     * @return null
     **/
    public function testGetEventNames()
    {
        $reflectionClass = new ReflectionClass("Accompli\\AccompliEvents");

        $this->assertSame(array_values($reflectionClass->getConstants() ), AccompliEvents::getEventNames());
    }
}
