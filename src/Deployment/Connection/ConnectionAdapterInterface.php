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
     * Uploads a file to a remote file.
     *
     * @param string $sourceFilename
     * @param string $destinationFilename
     *
     * @return bool
     */
    public function putFile($sourceFilename, $destinationFilename);

    /**
     * Set the $data to a file.
     *
     * @param string $destinationFilename
     * @param mixed  $data
     *
     * @return bool
     */
    public function putContents($destinationFilename, $data);
}
