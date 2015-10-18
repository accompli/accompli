<?php

namespace Accompli\Deployment\Connection;

use Symfony\Component\Process\Process;

/**
 * LocalConnectionAdapter.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class LocalConnectionAdapter implements ConnectionAdapterInterface
{
    /**
     * {@inheritdoc}
     */
    public function connect()
    {
        return true; // Does nothing
    }

    /**
     * {@inheritdoc}
     */
    public function disconnect()
    {
        return true; // Does nothing
    }

    /**
     * {@inheritdoc}
     */
    public function executeCommand($command)
    {
        $process = new Process($command);
        $process->run();

        return $process->getOutput(); // @todo change to status code and add reference argument?
    }

    /**
     * {@inheritdoc}
     */
    public function getContents($filename)
    {
        return file_get_contents($filename);
    }

    /**
     * {@inheritdoc}
     */
    public function putContents($destinationFilename, $data)
    {
        $result = file_put_contents($destinationFilename, $data);

        return ($result !== false);
    }

    /**
     * {@inheritdoc}
     */
    public function getFile($sourceFilename, $destinationFilename)
    {
        return $this->putFile($sourceFilename, $destinationFilename);
    }

    /**
     * {@inheritdoc}
     */
    public function putFile($sourceFilename, $destinationFilename)
    {
        return copy($sourceFilename, $destinationFilename);
    }

    /**
     * {@inheritdoc}
     */
    public function linkFile($remoteTarget, $remoteLink)
    {
        return symlink($remoteTarget, $remoteLink);
    }

    /**
     * {@inheritdoc}
     */
    public function renameFile($remoteSource, $remoteDestination)
    {
        return rename($remoteSource, $remoteDestination);
    }
}
