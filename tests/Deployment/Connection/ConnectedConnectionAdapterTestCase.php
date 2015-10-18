<?php

namespace Accompli\Test\Deployment\Connection;

/**
 * ConnectedConnectionAdapterTestCase.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
abstract class ConnectedConnectionAdapterTestCase extends ConnectionAdapterTestCase
{
    /**
     * Tests if ConnectionAdapterInterface::disconnect returns false without connection.
     */
    public function testDisconnectReturnsFalseWithoutConnection()
    {
        $this->assertFalse($this->connectionAdapter->disconnect());
    }

    /**
     * Tests if ConnectionAdapterInterface::executeCommand returns false without connection.
     */
    public function testExecuteCommandReturnsFalseWithoutConnection()
    {
        $this->assertFalse($this->connectionAdapter->executeCommand('echo test'));
    }

    /**
     * Tests if ConnectionAdapterInterface::getContents returns false without connection.
     */
    public function testGetContentsReturnsFalseWithoutConnection()
    {
        $this->assertFalse($this->connectionAdapter->getContents(__DIR__.'/test.txt'));
    }

    /**
     * Tests if ConnectionAdapterInterface::putContents returns false without connection.
     */
    public function testPutContentsReturnsFalseWithoutConnection()
    {
        $this->assertFalse($this->connectionAdapter->putContents(__DIR__.'/test.txt', 'test'));
    }

    /**
     * Tests if ConnectionAdapterInterface::getFile returns false without connection.
     */
    public function testGetFileReturnsFalseWithoutConnection()
    {
        $this->assertFalse($this->connectionAdapter->getFile(__DIR__.'/test.txt', __DIR__.'/test2.txt'));
    }

    /**
     * Tests if ConnectionAdapterInterface::putFile returns false without connection.
     */
    public function testPutFileReturnsFalseWithoutConnection()
    {
        $this->assertFalse($this->connectionAdapter->putFile(__DIR__.'/test.txt', __DIR__.'/test2.txt'));
    }

    /**
     * Tests if ConnectionAdapterInterface::linkFile returns false without connection.
     */
    public function testLinkFileReturnsFalseWithoutConnection()
    {
        $this->assertFalse($this->connectionAdapter->linkFile(__DIR__.'/test.txt', __DIR__.'/test2.txt'));
    }

    /**
     * Tests if ConnectionAdapterInterface::renameFile returns false without connection.
     */
    public function testRenameFileReturnsFalseWithoutConnection()
    {
        $this->assertFalse($this->connectionAdapter->renameFile(__DIR__.'/test.txt', __DIR__.'/test2.txt'));
    }
}
