<?php

namespace Accompli\Test;

use Accompli\Deployment\Host;
use PHPUnit_Framework_TestCase;
use UnexpectedValueException;

/**
 * HostTest.
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 */
class HostTest extends PHPUnit_Framework_TestCase
{
    /**
     * testConstructWithInvalidStageThrowsUnexpectedValueException.
     *
     * @expectedException UnexpectedValueException
     */
    public function testConstructWithInvalidStageThrowsUnexpectedValueException()
    {
        $this->createHostInstance('development');
    }

    /**
     * testGetterMethods.
     *
     * @dataProvider provideTestGetterMethods
     *
     * @param string $getterMethod
     * @param mixed  $expectedValue
     */
    public function testGetterMethods($getterMethod, $expectedValue)
    {
        $host = $this->createHostInstance();

        $this->assertSame($expectedValue, $host->$getterMethod());
    }

    /**
     * Tests if Host::getConnection returns null when no connection adapter instance has been set.
     */
    public function testGetConnectionReturnsNull()
    {
        $host = $this->createHostInstance();

        $this->assertNull($host->getConnection());
    }

    /**
     * Tests if Host::getConnection returns the connection adapter instance set with Host::setConnection.
     */
    public function testSetConnectionSetsConnectionAndIsReturnedByGetConnection()
    {
        $connectionMock = $this->getMockBuilder('Accompli\Deployment\Connection\ConnectionAdapterInterface')->getMock();

        $host = $this->createHostInstance();
        $host->setConnection($connectionMock);

        $this->assertSame($connectionMock, $host->getConnection());
    }

    /**
     * Tests if Host::hasConnection returns false when no connection adapter instance has been set.
     */
    public function testHasConnectionReturnsFalse()
    {
        $host = $this->createHostInstance();

        $this->assertFalse($host->hasConnection());
    }

    /**
     * Tests if Host::hasConnection returns true when a connection adapter instance has been set with Host::setConnection.
     */
    public function testHasConnectionReturnsTrueWhenConnectionInstanceIsSet()
    {
        $connectionMock = $this->getMockBuilder('Accompli\Deployment\Connection\ConnectionAdapterInterface')->getMock();

        $host = $this->createHostInstance();
        $host->setConnection($connectionMock);

        $this->assertTrue($host->hasConnection());
    }

    /**
     * testIsValidStage.
     *
     * @dataProvider provideTestIsValidStage
     *
     * @param string $stage
     * @param bool   $expectedResult
     */
    public function testIsValidStage($stage, $expectedResult)
    {
        $this->assertSame($expectedResult, Host::isValidStage($stage));
    }

    /**
     * Returns an array with testvalues for testGetterMethods.
     *
     * @return array
     */
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
     * Returns an array with testvalues for testIsValidStage.
     *
     * @return array
     */
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
     * Constructs and returns a new Host instance.
     *
     * @param string $stage
     * @param string $connectionType
     * @param string $hostname
     * @param string $path
     *
     * @return Host
     */
    private function createHostInstance($stage = Host::STAGE_TEST, $connectionType = 'local', $hostname = 'localhost', $path = '/var/www')
    {
        return new Host($stage, $connectionType, $hostname, $path);
    }
}
