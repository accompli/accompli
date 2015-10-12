<?php

namespace Accompli\Test;

use Accompli\Deployment\Connection\LocalConnectionAdapter;
use PHPUnit_Framework_TestCase;

/**
 * LocalConnectionAdapterTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class LocalConnectionAdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function tearDown()
    {
        if (file_exists(__DIR__ . '/test.txt')) {
            unlink(__DIR__ . '/test.txt');
        }
    }

    /**
     * Tests if LocalConnectionAdapter::connect returns true.
     */
    public function testConnectReturnsTrue()
    {
        $connectionAdapter = new LocalConnectionAdapter();

        $this->assertTrue($connectionAdapter->connect());
    }

    /**
     * Tests if LocalConnectionAdapter::executeCommand returns the expected output
     */
    public function testExecuteCommand()
    {
        $connectionAdapter = new LocalConnectionAdapter();

        $this->assertSame("test" . PHP_EOL, $connectionAdapter->executeCommand('echo test'));
    }

    /**
     *
     */
    public function testPutContents()
    {
        $connectionAdapter = new LocalConnectionAdapter();
        $connectionAdapter->putContents(__DIR__ . '/test.txt', 'test');

        $this->assertFileExists(__DIR__ . '/test.txt');
        $this->assertSame('test', file_get_contents(__DIR__ . '/test.txt'));
    }

    /**
     *
     */
    public function testGetContents()
    {
        $connectionAdapter = new LocalConnectionAdapter();
        $connectionAdapter->putContents(__DIR__ . '/test.txt', 'test');

        $this->assertSame('test', $connectionAdapter->getContents(__DIR__ . '/test.txt'));
    }
}
