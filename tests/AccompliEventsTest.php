<?php

namespace Accompli\Test;

use Accompli\AccompliEvents;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * AccompliEventsTest.
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 */
class AccompliEventsTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if AccompliEvents::getEventNames returns an array with all defined constants.
     */
    public function testGetEventNames()
    {
        $reflectionClass = new ReflectionClass(AccompliEvents::class);

        $this->assertSame(array_values($reflectionClass->getConstants()), AccompliEvents::getEventNames());
    }
}
