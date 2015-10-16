<?php

namespace Accompli\Test\Deployment\Connection;

use Accompli\Deployment\Connection\ConnectionAdapterInterface;
use PHPUnit_Framework_TestCase;

/**
 * ConnectionAdapterTestCase.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
abstract class ConnectionAdapterTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * Unlinks created test files.
     */
    public function tearDown()
    {
        if (file_exists(__DIR__.'/test.txt')) {
            unlink(__DIR__.'/test.txt');
        }
        if (file_exists(__DIR__.'/test2.txt') || is_link(__DIR__.'/test2.txt')) {
            unlink(__DIR__.'/test2.txt');
        }
    }

    /**
     * Tests if LocalConnectionAdapter::connect returns true.
     */
    public function testConnectReturnsTrue()
    {
        $connectionAdapter = $this->createConnectionAdapter();

        $this->assertTrue($connectionAdapter->connect());
    }

    /**
     * Tests if LocalConnectionAdapter::executeCommand returns the expected output.
     *
     * @depends testConnectReturnsTrue
     */
    public function testExecuteCommand()
    {
        $connectionAdapter = $this->createConnectionAdapter();
        $connectionAdapter->connect();

        $this->assertSame('test'.PHP_EOL, $connectionAdapter->executeCommand('echo test'));
    }

    /**
     * Tests if ConnectionAdapterInterface::getContents returns the data from a file.
     *
     * @depends testConnectReturnsTrue
     */
    public function testGetContents()
    {
        $connectionAdapter = $this->createConnectionAdapter();
        $connectionAdapter->connect();

        file_put_contents(__DIR__.'/test.txt', 'test');

        $result = $connectionAdapter->getContents(__DIR__.'/test.txt');

        $this->assertSame('test', $result);
    }

    /**
     * Tests if ConnectionAdapterInterface::putContents puts data in a file.
     *
     * @depends testConnectReturnsTrue
     */
    public function testPutContents()
    {
        $connectionAdapter = $this->createConnectionAdapter();
        $connectionAdapter->connect();
        $connectionAdapter->putContents(__DIR__.'/test.txt', 'test');

        $this->assertFileExists(__DIR__.'/test.txt');
        $this->assertSame('test', file_get_contents(__DIR__.'/test.txt'));
    }

    /**
     * Tests if ConnectionAdapterInterface::getFile returns a copy of a file.
     *
     * @depends testConnectReturnsTrue
     */
    public function testGetFile()
    {
        file_put_contents(__DIR__.'/test.txt', 'test');

        $connectionAdapter = $this->createConnectionAdapter();
        $connectionAdapter->connect();

        $result = $connectionAdapter->getFile(__DIR__.'/test.txt', __DIR__.'/test2.txt');

        $this->assertTrue($result);
        $this->assertFileExists(__DIR__.'/test2.txt');
        $this->assertSame('test', file_get_contents(__DIR__.'/test2.txt'));
    }

    /**
     * Tests if ConnectionAdapterInterface::putFile copies a file.
     *
     * @depends testConnectReturnsTrue
     */
    public function testPutFile()
    {
        file_put_contents(__DIR__.'/test.txt', 'test');

        $connectionAdapter = $this->createConnectionAdapter();
        $connectionAdapter->connect();

        $result = $connectionAdapter->putFile(__DIR__.'/test.txt', __DIR__.'/test2.txt');

        $this->assertTrue($result);
        $this->assertFileExists(__DIR__.'/test2.txt');
        $this->assertSame('test', file_get_contents(__DIR__.'/test2.txt'));
    }

    /**
     * Tests if ConnectionAdapterInterface::linkFile symlinks a remote file to a remote link.
     *
     * @depends testConnectReturnsTrue
     */
    public function testLinkFile()
    {
        file_put_contents(__DIR__.'/test.txt', 'test');

        $connectionAdapter = $this->createConnectionAdapter();
        $connectionAdapter->connect();

        $result = $connectionAdapter->linkFile(__DIR__.'/test.txt', __DIR__.'/test2.txt');

        $this->assertTrue($result);
        $this->assertTrue(is_link(__DIR__.'/test2.txt'));
    }

    /**
     * Tests if ConnectionAdapterInterface::renameFile renames/moves a remote file to another name or remote location.
     *
     * @depends testConnectReturnsTrue
     */
    public function testRenameFile()
    {
        file_put_contents(__DIR__.'/test.txt', 'test');

        $connectionAdapter = $this->createConnectionAdapter();
        $connectionAdapter->connect();

        $result = $connectionAdapter->renameFile(__DIR__.'/test.txt', __DIR__.'/test2.txt');

        $this->assertTrue($result);
        $this->assertFileNotExists(__DIR__.'/test.txt');
        $this->assertFileExists(__DIR__.'/test2.txt');
    }

    /**
     * Constructs a new connection adapter implementing the ConnectionAdapterInterface.
     *
     * @return ConnectionAdapterInterface
     */
    abstract protected function createConnectionAdapter();
}
