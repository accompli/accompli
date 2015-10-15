<?php

namespace Accompli\Deployment\Connection;

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
     * Executes a command.
     *
     * @param string $command
     *
     * @return bool
     */
    public function executeCommand($command);

    /**
     * Returns the contents of a remote file.
     *
     * @param string $filename
     *
     * @return string
     */
    public function getContents($filename);

    /**
     * Set the $data to a file.
     *
     * @param string $destinationFilename
     * @param mixed  $data
     *
     * @return bool
     */
    public function putContents($destinationFilename, $data);

    /**
     * Downloads a remote file to a local file.
     *
     * @param string $sourceFilename
     * @param string $destinationFilename
     *
     * @return bool
     */
    public function getFile($sourceFilename, $destinationFilename);

    /**
     * Uploads a local file to a remote file.
     *
     * @param string $sourceFilename
     * @param string $destinationFilename
     *
     * @return bool
     */
    public function putFile($sourceFilename, $destinationFilename);

    /**
     * Symlinks a remote file to a remote link.
     *
     * @param string $remoteTarget
     * @param string $remoteLink
     *
     * @return bool
     */
    public function linkFile($remoteTarget, $remoteLink);

    /**
     * Renames/moves a remote file or directory to another name or remote location.
     *
     * @param string $remoteSource
     * @param string $remoteDestination
     *
     * @return bool
     */
    public function renameFile($remoteSource, $remoteDestination);
}
