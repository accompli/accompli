<?php

namespace Accompli\Deployment\Connection;

use Accompli\Chrono\Process\ProcessExecutionResult;
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
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function disconnect()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isConnected()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isFile($remoteFilename)
    {
        return is_file($remoteFilename);
    }

    /**
     * {@inheritdoc}
     */
    public function isDirectory($remoteDirectory)
    {
        return is_dir($remoteDirectory);
    }

    /**
     * {@inheritdoc}
     */
    public function executeCommand($command)
    {
        $process = new Process($command);
        $process->run();

        return new ProcessExecutionResult($process->getExitCode(), $process->getOutput(), strval($process->getErrorOutput()));
    }

    /**
     * {@inheritdoc}
     */
    public function getDirectoryContentsList($remoteDirectory)
    {
        return array_values(array_diff(scandir($remoteDirectory, SCANDIR_SORT_ASCENDING), array('.', '..')));
    }

    /**
     * {@inheritdoc}
     */
    public function getContents($remoteFilename)
    {
        return file_get_contents($remoteFilename);
    }

    /**
     * {@inheritdoc}
     */
    public function getFile($remoteFilename, $localFilename)
    {
        return $this->copy($remoteFilename, $localFilename);
    }

    /**
     * {@inheritdoc}
     */
    public function createDirectory($remoteDirectory, $fileMode = 0770, $recursive = false)
    {
        return mkdir($remoteDirectory, $fileMode, $recursive);
    }

    /**
     * {@inheritdoc}
     */
    public function createFile($remoteFilename, $fileMode = 0770)
    {
        return (touch($remoteFilename) && $this->changePermissions($remoteFilename, $fileMode));
    }

    /**
     * {@inheritdoc}
     */
    public function link($remoteSource, $remoteTarget)
    {
        return symlink($remoteSource, $remoteTarget);
    }

    /**
     * {@inheritdoc}
     */
    public function move($remoteSource, $remoteDestination)
    {
        return rename($remoteSource, $remoteDestination);
    }

    /**
     * {@inheritdoc}
     */
    public function copy($remoteSource, $remoteDestination)
    {
        return copy($remoteSource, $remoteDestination);
    }

    /**
     * {@inheritdoc}
     */
    public function changePermissions($remoteTarget, $fileMode, $recursive = false)
    {
        if ($recursive === true && $this->isDirectory($remoteTarget)) {
            $result = true;
            $directoryItems = $this->getDirectoryContentsList($remoteTarget);
            foreach ($directoryItems as $directoryItem) {
                $directoryItem = $remoteTarget.'/'.$directoryItem;
                if ($this->isDirectory($directoryItem)) {
                    $result = ($result && $this->changePermissions($directoryItem, $fileMode, $recursive));
                } else {
                    $result = ($result && $this->changePermissions($directoryItem, $fileMode, false));
                }
            }

            return ($result && $this->changePermissions($remoteTarget, $fileMode, false));
        }

        return chmod($remoteTarget, $fileMode);
    }

    /**
     * {@inheritdoc}
     */
    public function putContents($remoteFilename, $data)
    {
        $result = file_put_contents($remoteFilename, $data);

        return ($result !== false);
    }

    /**
     * {@inheritdoc}
     */
    public function putFile($localFilename, $remoteFilename)
    {
        return $this->copy($localFilename, $remoteFilename);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($remoteTarget, $recursive = false)
    {
        if ($recursive === true && $this->isDirectory($remoteTarget)) {
            $result = true;
            $directoryItems = $this->getDirectoryContentsList($remoteTarget);
            foreach ($directoryItems as $directoryItem) {
                $directoryItem = $remoteTarget.'/'.$directoryItem;
                if ($this->isDirectory($directoryItem)) {
                    $result = ($result && $this->delete($directoryItem, $recursive));
                } else {
                    $result = ($result && $this->delete($directoryItem, false));
                }
            }

            return ($result && $this->delete($remoteTarget, false));
        }

        if ($this->isDirectory($remoteTarget)) {
            return rmdir($remoteTarget);
        }

        return unlink($remoteTarget);
    }
}
