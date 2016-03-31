<?php

namespace Accompli\Test\Console;

use Accompli\Accompli;
use Accompli\Console\Application;
use PHPUnit_Framework_TestCase;

/**
 * ApplicationTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class ApplicationTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if constructing a new Application sets the properties.
     */
    public function testConstruct()
    {
        $application = new Application();

        $this->assertAttributeSame('Accompli', 'name', $application);
        $this->assertAttributeSame(Accompli::VERSION, 'version', $application);
    }

    /**
     * Tests if Application::getHelp returns the Accompli logo.
     */
    public function testGetHelp()
    {
        $application = new Application();

        $this->assertStringStartsWith(Accompli::LOGO, $application->getHelp());
    }

    /**
     * Tests if Application::has returns true for the commands added in Application::getDefaultCommands.
     *
     * @dataProvider provideTestGetDefaultCommands
     *
     * @param string $commandName
     */
    public function testGetDefaultCommands($commandName)
    {
        $application = new Application();

        $this->assertTrue($application->has($commandName));
    }

    /**
     * Returns an array with test cases for @see testGetDefaultCommands.
     *
     * @return array
     */
    public function provideTestGetDefaultCommands()
    {
        return array(
            array('install-release'),
            array('deploy-release'),
        );
    }
}
