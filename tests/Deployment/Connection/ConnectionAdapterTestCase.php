<?php

namespace Accompli\Test\Deployment\Connection;

use Accompli\Deployment\Connection\ConnectionAdapterInterface;
use Accompli\Test\WorkspaceUtility;
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
     * @var WorkspaceUtility
     */
    protected $workspaceUtility;

    /**
     * Create a new connection adapter instance.
     */
    public function setUp()
    {
        $this->connectionAdapter = $this->createConnectionAdapter();

        $this->workspaceUtility = new WorkspaceUtility();
        $this->workspaceUtility->create();
    }

    /**
     * Disconnects a connection adapter instance and removes created test files.
     */
    public function tearDown()
    {
        if ($this->connectionAdapter instanceof ConnectionAdapterInterface) {
            $this->connectionAdapter->disconnect();
        }

        $this->workspaceUtility->remove();
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
     * Tests if ConnectionAdapterInterface::isConnected returns true when connected.
     */
    public function testIsConnectedReturnsTrueWhenConnected()
    {
        $this->connectionAdapter->connect();

        $this->assertTrue($this->connectionAdapter->isConnected());
    }

    /**
     * Tests if ConnectionAdapterInterface::isFile returns false when the file does not exist.
     *
     * @depends testConnectReturnsTrue
     */
    public function testIsFileReturnsFalseWhenFileNotExists()
    {
        $this->connectionAdapter->connect();

        $this->assertFalse($this->connectionAdapter->isFile($this->workspaceUtility->getWorkspacePath().'test.txt'));
    }

    /**
     * Tests if ConnectionAdapterInterface::isFile returns true when the file exists.
     *
     * @depends testConnectReturnsTrue
     */
    public function testIsFileReturnsTrueWhenFileExists()
    {
        $this->workspaceUtility->createFile('/test.txt');

        $this->connectionAdapter->connect();

        $this->assertTrue($this->connectionAdapter->isFile($this->workspaceUtility->getWorkspacePath().'/test.txt'));
    }

    /**
     * Tests if ConnectionAdapterInterface::isLink returns false when the link does not exist.
     *
     * @depends testConnectReturnsTrue
     */
    public function testIsLinkReturnsFalseWhenLinkNotExists()
    {
        $this->connectionAdapter->connect();

        $this->assertFalse($this->connectionAdapter->isLink($this->workspaceUtility->getWorkspacePath().'testLink'));
    }

    /**
     * Tests if ConnectionAdapterInterface::isLink returns true when the link exists.
     *
     * @depends testConnectReturnsTrue
     */
    public function testIsLinkReturnsTrueWhenLinkExists()
    {
        $this->workspaceUtility->createFile('/test.txt');
        symlink($this->workspaceUtility->getWorkspacePath().'/test.txt', $this->workspaceUtility->getWorkspacePath().'/testLink');

        $this->connectionAdapter->connect();

        $this->assertTrue($this->connectionAdapter->isLink($this->workspaceUtility->getWorkspacePath().'/testLink'));
    }

    /**
     * Tests if ConnectionAdapterInterface::isDirectory returns false when the directory does not exist.
     *
     * @depends testConnectReturnsTrue
     */
    public function testIsDirectoryReturnsFalseWhenDirectoryNotExists()
    {
        $this->connectionAdapter->connect();

        $this->assertFalse($this->connectionAdapter->isDirectory($this->workspaceUtility->getWorkspacePath().'/non-existing-directory/'));
    }

    /**
     * Tests if ConnectionAdapterInterface::isDirectory returns false when the path is a file.
     *
     * @depends testConnectReturnsTrue
     */
    public function testIsDirectoryReturnsFalseWhenDirectoryIsFile()
    {
        $this->workspaceUtility->createFile('/test.txt');

        $this->connectionAdapter->connect();

        $this->assertFalse($this->connectionAdapter->isDirectory($this->workspaceUtility->getWorkspacePath().'/test.txt'));
    }

    /**
     * Tests if ConnectionAdapterInterface::isDirectory returns true when the directory exists.
     *
     * @depends testConnectReturnsTrue
     */
    public function testIsDirectoryReturnsTrueWhenDirectoryExists()
    {
        $this->workspaceUtility->createDirectory('/existing-directory/');

        $this->connectionAdapter->connect();

        $this->assertTrue($this->connectionAdapter->isDirectory($this->workspaceUtility->getWorkspacePath().'/existing-directory'));
    }

    /**
     * Tests if ConnectionAdapterInterface::changeWorkingDirectory returns true on successful change to a different working directory.
     *
     * @depends testConnectReturnsTrue
     */
    public function testChangeWorkingDirectoryReturnsTrue()
    {
        $this->connectionAdapter->connect();

        $this->assertTrue($this->connectionAdapter->changeWorkingDirectory($this->workspaceUtility->getWorkspacePath()));
    }

    /**
     * Tests if ConnectionAdapterInterface::changeWorkingDirectory returns false when trying to change to a non-existing working directory.
     *
     * @depends testChangeWorkingDirectoryReturnsTrue
     */
    public function testChangeWorkingDirectoryWithNonExistingDirectoryReturnsFalse()
    {
        $this->connectionAdapter->connect();

        $this->assertFalse($this->connectionAdapter->changeWorkingDirectory($this->workspaceUtility->getWorkspacePath().'/non-existing-directory'));
    }

    /**
     * Tests if ConnectionAdapterInterface::executeCommand returns the expected output.
     *
     * @depends testDisconnectReturnsTrue
     */
    public function testExecuteCommand()
    {
        $this->connectionAdapter->connect();

        $result = $this->connectionAdapter->executeCommand('echo test');
        $this->assertInstanceOf('Accompli\Chrono\Process\ProcessExecutionResult', $result);
        $this->assertSame(0, $result->getExitCode());
        $this->assertSame('test'.PHP_EOL, $result->getOutput());
        $this->assertSame('', $result->getErrorOutput());
    }

    /**
     * Tests if ConnectionAdapterInterface::getWorkingDirectory returns the expected working directory.
     *
     * @depends testChangeWorkingDirectoryWithNonExistingDirectoryReturnsFalse
     */
    public function testGetWorkingDirectory()
    {
        $this->connectionAdapter->connect();
        $this->connectionAdapter->changeWorkingDirectory($this->workspaceUtility->getWorkspacePath());

        $this->assertSame(realpath($this->workspaceUtility->getWorkspacePath()), $this->connectionAdapter->getWorkingDirectory());
    }

    /**
     * Tests if ConnectionAdapterInterface::getDirectoryContentsList returns the expected array.
     *
     * @depends testConnectReturnsTrue
     */
    public function testGetDirectoryContentsList()
    {
        $this->workspaceUtility->createDirectory('/existing-directory/subdirectory', true);
        $this->workspaceUtility->createFile('/existing-directory/test.txt');

        $expectedResult = array(
            'subdirectory',
            'test.txt',
        );

        $this->connectionAdapter->connect();

        $this->assertSame($expectedResult, $this->connectionAdapter->getDirectoryContentsList($this->workspaceUtility->getWorkspacePath().'/existing-directory/'));
    }

    /**
     * Tests if ConnectionAdapterInterface::getContents returns the data from a file.
     *
     * @depends testConnectReturnsTrue
     */
    public function testGetContents()
    {
        $this->workspaceUtility->createFile('/test.txt', 'test');

        $this->connectionAdapter->connect();

        $result = $this->connectionAdapter->getContents($this->workspaceUtility->getWorkspacePath().'/test.txt');

        $this->assertSame('test', $result);
    }

    /**
     * Tests if ConnectionAdapterInterface::getFile returns a copy of a file.
     *
     * @depends testConnectReturnsTrue
     */
    public function testGetFile()
    {
        $this->workspaceUtility->createFile('/test.txt', 'test');

        $this->connectionAdapter->connect();

        $this->assertTrue($this->connectionAdapter->getFile($this->workspaceUtility->getWorkspacePath().'/test.txt', $this->workspaceUtility->getWorkspacePath().'/test2.txt'));
        $this->assertFileExists($this->workspaceUtility->getWorkspacePath().'/test2.txt');
        $this->assertSame('test', file_get_contents($this->workspaceUtility->getWorkspacePath().'/test2.txt'));
    }

    /**
     * Tests if ConnectionAdapterInterface::createDirectory creates a new directory.
     *
     * @depends testConnectReturnsTrue
     */
    public function testCreateDirectory()
    {
        $this->connectionAdapter->connect();

        $this->assertTrue($this->connectionAdapter->createDirectory($this->workspaceUtility->getWorkspacePath().'/existing-directory'));
        $this->assertTrue(is_dir($this->workspaceUtility->getWorkspacePath().'/existing-directory'));
    }

    /**
     * Tests if ConnectionAdapterInterface::createFile creates a new file.
     *
     * @depends testConnectReturnsTrue
     */
    public function testCreateFile()
    {
        $this->connectionAdapter->connect();

        $this->assertTrue($this->connectionAdapter->createFile($this->workspaceUtility->getWorkspacePath().'/test.txt'));
        $this->assertFileExists($this->workspaceUtility->getWorkspacePath().'/test.txt');
    }

    /**
     * Tests if ConnectionAdapterInterface::link symlinks a remote file to a remote link.
     *
     * @depends testDisconnectReturnsTrue
     */
    public function testLink()
    {
        $this->workspaceUtility->createFile('/test.txt');

        $this->connectionAdapter->connect();

        $this->assertTrue($this->connectionAdapter->link($this->workspaceUtility->getWorkspacePath().'/test.txt', $this->workspaceUtility->getWorkspacePath().'/test2.txt'));
        $this->assertTrue(is_link($this->workspaceUtility->getWorkspacePath().'/test2.txt'));
    }

    /**
     * Tests if ConnectionAdapterInterface::move moves/renames a remote file to another name or remote location.
     *
     * @depends testDisconnectReturnsTrue
     */
    public function testMove()
    {
        $this->workspaceUtility->createFile('/test.txt');

        $this->connectionAdapter->connect();

        $this->assertTrue($this->connectionAdapter->move($this->workspaceUtility->getWorkspacePath().'/test.txt', $this->workspaceUtility->getWorkspacePath().'/test2.txt'));
        $this->assertFileNotExists($this->workspaceUtility->getWorkspacePath().'/test.txt');
        $this->assertFileExists($this->workspaceUtility->getWorkspacePath().'/test2.txt');
    }

    /**
     * Tests if ConnectionAdapterInterface::copy copies a remote file to another name or remote location.
     *
     * @depends testConnectReturnsTrue
     */
    public function testCopy()
    {
        $this->workspaceUtility->createFile('/test.txt');

        $this->connectionAdapter->connect();

        $this->assertTrue($this->connectionAdapter->copy($this->workspaceUtility->getWorkspacePath().'/test.txt', $this->workspaceUtility->getWorkspacePath().'/test2.txt'));
        $this->assertFileExists($this->workspaceUtility->getWorkspacePath().'/test2.txt');
        $this->assertFileEquals($this->workspaceUtility->getWorkspacePath().'/test.txt', $this->workspaceUtility->getWorkspacePath().'/test2.txt');
    }

    /**
     * Tests if ConnectionAdapterInterface::changeFileMode changes the permissions of a file.
     *
     * @depends testConnectReturnsTrue
     */
    public function testChangePermissions()
    {
        $this->workspaceUtility->createFile('/test.txt');

        $this->connectionAdapter->connect();

        $this->assertTrue($this->connectionAdapter->changePermissions($this->workspaceUtility->getWorkspacePath().'/test.txt', 0700));

        clearstatcache(true, $this->workspaceUtility->getWorkspacePath().'/existing-directory/test.txt');
        $this->assertSame('0700', substr(sprintf('%o', fileperms($this->workspaceUtility->getWorkspacePath().'/test.txt')), -4));
    }

    /**
     * @depends testConnectReturnsTrue
     */
    public function testChangePermissionsRecursive()
    {
        $this->workspaceUtility->createDirectory('/existing-directory/subdirectory', true);
        $this->workspaceUtility->createFile('/existing-directory/test.txt');

        $this->connectionAdapter->connect();

        $this->assertTrue($this->connectionAdapter->changePermissions($this->workspaceUtility->getWorkspacePath().'/existing-directory', 0700, true));

        clearstatcache(true, $this->workspaceUtility->getWorkspacePath().'/existing-directory/test.txt');
        $this->assertSame('0700', substr(sprintf('%o', fileperms($this->workspaceUtility->getWorkspacePath().'/existing-directory/test.txt')), -4));
    }

    /**
     * Tests if ConnectionAdapterInterface::putContents puts data in a file.
     *
     * @depends testDisconnectReturnsTrue
     */
    public function testPutContents()
    {
        $this->connectionAdapter->connect();

        $this->assertTrue($this->connectionAdapter->putContents($this->workspaceUtility->getWorkspacePath().'/test.txt', 'test'));
        $this->assertFileExists($this->workspaceUtility->getWorkspacePath().'/test.txt');
        $this->assertSame('test', file_get_contents($this->workspaceUtility->getWorkspacePath().'/test.txt'));
    }

    /**
     * Tests if ConnectionAdapterInterface::putFile copies a file.
     *
     * @depends testConnectReturnsTrue
     */
    public function testPutFile()
    {
        $this->workspaceUtility->createFile('/test.txt', 'test');

        $this->connectionAdapter->connect();

        $this->assertTrue($this->connectionAdapter->putFile($this->workspaceUtility->getWorkspacePath().'/test.txt', $this->workspaceUtility->getWorkspacePath().'/test2.txt'));
        $this->assertFileExists($this->workspaceUtility->getWorkspacePath().'/test2.txt');
        $this->assertSame('test', file_get_contents($this->workspaceUtility->getWorkspacePath().'/test2.txt'));
    }

    /**
     * Tests if ConnectionAdapterInterface::delete deletes directories and files.
     *
     * @depends testConnectReturnsTrue
     * @depends testGetDirectoryContentsList
     */
    public function testDelete()
    {
        $this->workspaceUtility->createDirectory('/existing-directory/subdirectory', true);
        $this->workspaceUtility->createFile('/existing-directory/test.txt');

        $this->connectionAdapter->connect();

        $this->assertTrue($this->connectionAdapter->delete($this->workspaceUtility->getWorkspacePath().'/existing-directory', true));
        $this->assertFileNotExists($this->workspaceUtility->getWorkspacePath().'/existing-directory');
    }

    /**
     * Constructs a new connection adapter implementing the ConnectionAdapterInterface.
     *
     * @return ConnectionAdapterInterface
     */
    abstract protected function createConnectionAdapter();
}
