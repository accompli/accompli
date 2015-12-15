<?php

namespace Accompli\Deployment\Connection;

use Accompli\Chrono\Process\ProcessExecutionResult;

/**
 * ConnectionAdapterInterface.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
interface ConnectionAdapterInterface
{
    /**
     * Connects the adapter.
     *
     * @return bool
     */
    public function connect();

    /**
     * Disconnects the adapter.
     *
     * @return bool
     */
    public function disconnect();

    /**
     * Returns true when the adapter is connected.
     *
     * @return bool
     */
    public function isConnected();

    /**
     * Returns true if $remoteFilename is a remote file.
     *
     * @param string $remoteFilename
     *
     * @return bool
     */
    public function isFile($remoteFilename);

    /**
     * Returns true if $remoteTarget is a symlink.
     *
     * @param string $remoteTarget
     *
     * @return bool
     */
    public function isLink($remoteTarget);

    /**
     * Returns true if $remoteDirectory is a remote directory.
     *
     * @param string $remoteDirectory
     *
     * @return bool
     */
    public function isDirectory($remoteDirectory);

    /**
     * Changes the current working directory.
     *
     * @param string $remoteDirectory
     *
     * @return bool
     */
    public function changeWorkingDirectory($remoteDirectory);

    /**
     * Executes a command.
     *
     * @param string $command
     *
     * @return ProcessExecutionResult
     */
    public function executeCommand($command);

    /**
     * Returns the current working directory.
     *
     * @return string|bool
     */
    public function getWorkingDirectory();

    /**
     * Returns an array with files and directories within a remote directory.
     *
     * @param string $remoteDirectory
     *
     * @return array
     */
    public function getDirectoryContentsList($remoteDirectory);

    /**
     * Returns the contents of a remote file.
     *
     * @param string $remoteFilename
     *
     * @return string
     */
    public function getContents($remoteFilename);

    /**
     * Downloads a remote file to a local file.
     *
     * @param string $remoteFilename
     * @param string $localFilename
     *
     * @return bool
     */
    public function getFile($remoteFilename, $localFilename);

    /**
     * Creates a remote directory.
     *
     * @param string $remoteDirectory
     * @param int    $fileMode
     * @param bool   $recursive
     *
     * @return bool
     */
    public function createDirectory($remoteDirectory, $fileMode = 0770, $recursive = false);

    /**
     * Creates an empty remote file.
     *
     * @param string $remoteFilename
     * @param int    $fileMode
     *
     * @return bool
     */
    public function createFile($remoteFilename, $fileMode = 0770);

    /**
     * Creates a symbolic link to an existing remote file or directory at a remote location.
     *
     * @param string $remoteSource
     * @param string $remoteTarget
     *
     * @return bool
     */
    public function link($remoteSource, $remoteTarget);

    /**
     * Moves/renames a remote file or directory to another name or remote location.
     *
     * @param string $remoteSource
     * @param string $remoteDestination
     *
     * @return bool
     */
    public function move($remoteSource, $remoteDestination);

    /**
     * Copies a remote file or directory to another name or remote location.
     *
     * @param string $remoteSource
     * @param string $remoteDestination
     *
     * @return bool
     */
    public function copy($remoteSource, $remoteDestination);

    /**
     * Changes the permissions of a remote file or directory.
     *
     * @param string $remoteTarget
     * @param int    $fileMode
     * @param bool   $recursive
     *
     * @return bool
     */
    public function changePermissions($remoteTarget, $fileMode, $recursive = false);

    /**
     * Sets the $data to a remote file.
     *
     * @param string $remoteFilename
     * @param mixed  $data
     *
     * @return bool
     */
    public function putContents($remoteFilename, $data);

    /**
     * Uploads a local file to a remote file.
     *
     * @param string $localFilename
     * @param string $remoteFilename
     *
     * @return bool
     */
    public function putFile($localFilename, $remoteFilename);

    /**
     * Deletes a remote file or directory.
     *
     * @param string $remoteTarget
     * @param bool   $recursive
     *
     * @return bool
     */
    public function delete($remoteTarget, $recursive = false);
}
