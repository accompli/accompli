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
     * The connection adapter instance implementing the ConnectionAdapterInterface.
     *
     * @var ConnectionAdapterInterface
     */
    protected $connectionAdapter;

    /**
     * Create a new connection adapter instance.
     */
    public function setUp()
    {
        $this->connectionAdapter = $this->createConnectionAdapter();
    }

    /**
     * Disconnects a connection adapter instance and unlinks created test files.
     */
    public function tearDown()
    {
        if ($this->connectionAdapter instanceof ConnectionAdapterInterface) {
            $this->connectionAdapter->disconnect();
        }

        if (file_exists(__DIR__.'/test.txt')) {
            unlink(__DIR__.'/test.txt');
        }
        if (file_exists(__DIR__.'/test2.txt') || is_link(__DIR__.'/test2.txt')) {
            unlink(__DIR__.'/test2.txt');
        }
    }

    /**
     * Tests if ConnectionAdapterInterface::connect returns true.
     */
    public function testConnectReturnsTrue()
    {
        $this->assertTrue($this->connectionAdapter->connect());
    }

    /**
     * Tests if ConnectionAdapterInterface::disconnect returns true.
     *
     * @depends testConnectReturnsTrue
     */
    public function testDisconnectReturnsTrue()
    {
        $this->connectionAdapter->connect();

        $this->assertTrue($this->connectionAdapter->disconnect());
    }

    /**
     * Tests if ConnectionAdapterInterface::executeCommand returns the expected output.
     *
     * @depends testDisconnectReturnsTrue
     */
    public function testExecuteCommand()
    {
        $this->connectionAdapter->connect();

        $this->assertSame('test'.PHP_EOL, $this->connectionAdapter->executeCommand('echo test'));
    }

    /**
     * Tests if ConnectionAdapterInterface::getContents returns the data from a file.
     *
     * @depends testDisconnectReturnsTrue
     */
    public function testGetContents()
    {
        $this->connectionAdapter->connect();

        file_put_contents(__DIR__.'/test.txt', 'test');

        $result = $this->connectionAdapter->getContents(__DIR__.'/test.txt');

        $this->assertSame('test', $result);
    }

    /**
     * Tests if ConnectionAdapterInterface::putContents puts data in a file.
     *
     * @depends testDisconnectReturnsTrue
     */
    public function testPutContents()
    {
        $this->connectionAdapter->connect();

        $this->assertTrue($this->connectionAdapter->putContents(__DIR__.'/test.txt', 'test'));
        $this->assertFileExists(__DIR__.'/test.txt');
        $this->assertSame('test', file_get_contents(__DIR__.'/test.txt'));
    }

    /**
     * Tests if ConnectionAdapterInterface::getFile returns a copy of a file.
     *
     * @depends testDisconnectReturnsTrue
     */
    public function testGetFile()
    {
        $this->connectionAdapter->connect();

        file_put_contents(__DIR__.'/test.txt', 'test');

        $this->assertTrue($this->connectionAdapter->getFile(__DIR__.'/test.txt', __DIR__.'/test2.txt'));
        $this->assertFileExists(__DIR__.'/test2.txt');
        $this->assertSame('test', file_get_contents(__DIR__.'/test2.txt'));
    }

    /**
     * Tests if ConnectionAdapterInterface::putFile copies a file.
     *
     * @depends testDisconnectReturnsTrue
     */
    public function testPutFile()
    {
        $this->connectionAdapter->connect();

        file_put_contents(__DIR__.'/test.txt', 'test');

        $this->assertTrue($this->connectionAdapter->putFile(__DIR__.'/test.txt', __DIR__.'/test2.txt'));
        $this->assertFileExists(__DIR__.'/test2.txt');
        $this->assertSame('test', file_get_contents(__DIR__.'/test2.txt'));
    }

    /**
     * Tests if ConnectionAdapterInterface::linkFile symlinks a remote file to a remote link.
     *
     * @depends testDisconnectReturnsTrue
     */
    public function testLinkFile()
    {
        $this->connectionAdapter->connect();

        file_put_contents(__DIR__.'/test.txt', 'test');

        $this->assertTrue($this->connectionAdapter->linkFile(__DIR__.'/test.txt', __DIR__.'/test2.txt'));
        $this->assertTrue(is_link(__DIR__.'/test2.txt'));
    }

    /**
     * Tests if ConnectionAdapterInterface::renameFile renames/moves a remote file to another name or remote location.
     *
     * @depends testDisconnectReturnsTrue
     */
    public function testRenameFile()
    {
        $this->connectionAdapter->connect();

        file_put_contents(__DIR__.'/test.txt', 'test');

        $this->assertTrue($this->connectionAdapter->renameFile(__DIR__.'/test.txt', __DIR__.'/test2.txt'));
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
