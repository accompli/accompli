<?php

namespace Accompli\Deployment\Connection;

use Accompli\Chrono\Process\ProcessExecutionResult;
use Accompli\Process\InteractiveProcess;
use Accompli\Utility\ProcessUtility;

/**
 * NativeSSHConnectionAdapter.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class NativeSSHConnectionAdapter extends AbstractSSHConnectionAdapter
{
    /**
     * The location of the SSH configuration file.
     *
     * @var string
     */
    private $configurationFile;

    /**
     * The InteractiveProcess instance containing the SSH process.
     *
     * @var InteractiveProcess
     */
    private $process;

    /**
     * Constructs a new NativeSSHConnectionAdapter instance.
     *
     * @param string      $hostname
     * @param string|null $authenticationUsername
     * @param string      $configurationFile
     */
    public function __construct($hostname, $authenticationUsername = null, $configurationFile = '~/.ssh/config')
    {
        $this->hostname = $hostname;
        $this->authenticationUsername = $authenticationUsername;
        if (isset($this->authenticationUsername) === false) {
            $this->authenticationUsername = $this->getCurrentUsername();
        }
        $this->configurationFile = $configurationFile;
    }

    /**
     * {@inheritdoc}
     */
    public function connect()
    {
        if ($this->isConnected()) {
            return true;
        }

        $arguments = array(
            $this->hostname,
            '-tt' => null,
        );

        if (isset($this->authenticationUsername)) {
            $arguments = array_merge(array('-l' => $this->authenticationUsername), $arguments);
        }

        $configurationFile = preg_replace('/^~/', $this->getUserDirectory(), $this->configurationFile);
        if (is_file($configurationFile)) {
            $arguments = array_merge(array('-F' => $configurationFile), $arguments);
        }

        $this->process = new InteractiveProcess(ProcessUtility::escapeArguments($arguments, 'ssh', ''));
        $this->process->setTimeout(null);
        $this->process->setIdleTimeout(5);
        $this->process->start();

        $this->process->read($this->getShellPromptRegex());

        return $this->process->isRunning();
    }

    /**
     * {@inheritdoc}
     */
    public function disconnect()
    {
        if ($this->isConnected()) {
            $this->process->write("logout\n");
            $this->process->stop();

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isConnected()
    {
        if ($this->process instanceof InteractiveProcess) {
            return $this->process->isRunning();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isFile($remoteFilename)
    {
        if ($this->isConnected()) {
            return $this->executeCommand(sprintf('[ ! -L "%s" ] && [ -f "%s" ]', $remoteFilename, $remoteFilename))->isSuccessful();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isLink($remoteTarget)
    {
        if ($this->isConnected()) {
            return $this->executeCommand(sprintf('[ -L "%s" ]', $remoteTarget))->isSuccessful();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isDirectory($remoteDirectory)
    {
        if ($this->isConnected()) {
            return $this->executeCommand(sprintf('[ ! -L "%s" ] && [ -d "%s" ]', $remoteDirectory, $remoteDirectory))->isSuccessful();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function readLink($remoteTarget)
    {
        if ($this->isConnected()) {
            $result = $this->executeCommand('readlink', array($remoteTarget));
            if ($result->isSuccessful()) {
                return trim($result->getOutput());
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function changeWorkingDirectory($remoteDirectory)
    {
        if ($this->isConnected()) {
            return $this->executeCommand('cd', array($remoteDirectory))->isSuccessful();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function executeCommand($command, array $arguments = array())
    {
        if ($this->isConnected()) {
            if (empty($arguments) === false) {
                $command = ProcessUtility::escapeArguments($arguments, $command);
            }

            $this->process->write($command."\n");
            $output = $this->getFilteredOutput($this->process->read($this->getShellPromptRegex()), $command);

            $this->process->write("echo $?\n");

            $exitCode = intval($this->getFilteredOutput($this->process->read($this->getShellPromptRegex()), 'echo $?'));
            $errorOutput = '';

            return new ProcessExecutionResult($exitCode, $output, $errorOutput);
        }

        return new ProcessExecutionResult(126, '', "Connection adapter not connected.\n");
    }

    /**
     * {@inheritdoc}
     */
    public function getWorkingDirectory()
    {
        if ($this->isConnected()) {
            $result = $this->executeCommand('pwd');
            if ($result->isSuccessful()) {
                return trim($result->getOutput());
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getDirectoryContentsList($remoteDirectory)
    {
        if ($this->isConnected()) {
            $result = $this->executeCommand(sprintf('ls -1A --color=never "%s"', $remoteDirectory));
            if ($result->isSuccessful()) {
                return $result->getOutputAsArray();
            }
        }

        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getContents($remoteFilename)
    {
        if ($this->isConnected()) {
            $result = $this->executeCommand('cat', array($remoteFilename));
            if ($result->isSuccessful()) {
                return $result->getOutput();
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getFile($remoteFilename, $localFilename)
    {
        if ($this->isConnected()) {
            $remoteContents = $this->getContents($remoteFilename);
            if ($remoteContents !== false) {
                return @file_put_contents($localFilename, $remoteContents) !== false;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function createDirectory($remoteDirectory, $fileMode = 0770, $recursive = false)
    {
        if ($this->isConnected()) {
            $arguments = array(
                sprintf('--mode=%o', $fileMode),
                $remoteDirectory,
            );

            if ($recursive === true) {
                array_unshift($arguments, '--parents');
            }

            return $this->executeCommand('mkdir', $arguments)->isSuccessful();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function createFile($remoteFilename, $fileMode = 0770)
    {
        if ($this->isConnected()) {
            return ($this->executeCommand('touch', array($remoteFilename))->isSuccessful() && $this->changePermissions($remoteFilename, $fileMode));
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function link($remoteSource, $remoteTarget)
    {
        if ($this->isConnected()) {
            return $this->executeCommand(sprintf('ln -s "%s" "%s"', $remoteSource, $remoteTarget))->isSuccessful();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function move($remoteSource, $remoteDestination)
    {
        if ($this->isConnected()) {
            return $this->executeCommand('mv', array($remoteSource, $remoteDestination))->isSuccessful();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function changePermissions($remoteTarget, $fileMode, $recursive = false)
    {
        if ($this->isConnected()) {
            $arguments = array(
                sprintf('%o', $fileMode),
                $remoteTarget,
            );

            if ($recursive === true) {
                array_unshift($arguments, '--recursive');
            }

            return $this->executeCommand('chmod', $arguments)->isSuccessful();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($remoteTarget, $recursive = false)
    {
        if ($this->isConnected()) {
            $arguments = array(
                $remoteTarget,
            );

            if ($recursive === true) {
                array_unshift($arguments, '--recursive');
            }

            return $this->executeCommand('rm', $arguments)->isSuccessful();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function getOutputFilterRegex($command)
    {
        $regex = parent::getOutputFilterRegex($command);

        return str_replace('(.*)', '(.*)\033]0;', $regex);
    }
}
