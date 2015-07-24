<?php

namespace Accompli\Test;

use Accompli\Deployment\Host;
use PHPUnit_Framework_TestCase;
use UnexpectedValueException;

/**
 * HostTest
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 * @package Accompli\Test
 */
class HostTest extends PHPUnit_Framework_TestCase
{
    /**
     * testConstructWithInvalidStageThrowsUnexpectedValueException
     *
     * @expectedException UnexpectedValueException
     *
     * @access public
     * @return null
     **/
    public function testConstructWithInvalidStageThrowsUnexpectedValueException()
    {
        $this->createHostInstance('development');
    }

    /**
     * testGetterMethods
     *
     * @dataProvider provideTestGetterMethods
     *
     * @access public
     * @param  string $getterMethod
     * @param  mixed  $expectedValue
     * @return null
     **/
    public function testGetterMethods($getterMethod, $expectedValue)
    {
        $host = $this->createHostInstance();

        $this->assertSame($expectedValue, $host->$getterMethod());
    }

    /**
     * testIsValidStage
     *
     * @dataProvider provideTestIsValidStage
     *
     * @access public
     * @param  string  $stage
     * @param  boolean $expectedResult
     * @return null
     **/
    public function testIsValidStage($stage, $expectedResult)
    {
        $this->assertSame($expectedResult, Host::isValidStage($stage));
    }

    /**
     * provideTestGetterMethods
     *
     * Returns an array with testvalues for testGetterMethods
     *
     * @access public
     * @return array
     **/
    public function provideTestGetterMethods()
    {
        return array(
            array('getStage', 'test'),
            array('getConnectionType', 'local'),
            array('getHostname', 'localhost'),
            array('getPath', '/var/www'),
        );
    }

    /**
     * provideTestGetterMethods
     *
     * Returns an array with testvalues for testIsValidStage
     *
     * @access public
     * @return array
     **/
    public function provideTestIsValidStage()
    {
        return array(
            array('development', false),
            array(Host::STAGE_TEST, true),
            array(Host::STAGE_ACCEPTANCE, true),
            array(Host::STAGE_PRODUCTION, true),
        );
    }

    /**
     * createHostInstance
     *
     * Constructs and returns a new Host instance
     *
     * @access private
     * @param  string $stage
     * @param  string $connectionType
     * @param  string $hostname
     * @param  string $path
     * @return Host
     */
    private function createHostInstance($stage = Host::STAGE_TEST, $connectionType = 'local', $hostname = 'localhost', $path = '/var/www')
    {
        return new Host($stage, $connectionType, $hostname, $path);
    }
}
