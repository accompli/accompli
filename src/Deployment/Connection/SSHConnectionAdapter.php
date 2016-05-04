<?php

namespace Accompli\Deployment\Connection;

use Accompli\Chrono\Process\ProcessExecutionResult;
use Accompli\Utility\ProcessUtility;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SFTP;
use phpseclib\System\SSH\Agent;
use UnexpectedValueException;

/**
 * SSHConnectionAdapter.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class SSHConnectionAdapter implements ConnectionAdapterInterface
{
    /**
     * The password authentication type.
     */
    const AUTHENTICATION_PASSWORD = 'password';

    /**
     * The public key authentication type.
     */
    const AUTHENTICATION_PUBLIC_KEY = 'publickey';

    /**
     * The SSH agent authentication type.
     */
    const AUTHENTICATION_SSH_AGENT = 'agent';

    /**
     * The hostname to connect to.
     *
     * @var string
     */
    private $hostname;

    /**
     * The username used for authentication.
     *
     * @var string
     */
    private $authenticationUsername;

    /**
     * The authentication credentials.
     *
     * @var RSA|string
     */
    private $authenticationCredentials;

    /**
     * The SSH FTP instance.
     *
     * @var SFTP
     */
    private $connection;

    /**
     * Constructs a new SSHConnectionAdapter instance.
     *
     * @param string      $hostname
     * @param string      $authenticationType
     * @param string|null $authenticationUsername
     * @param string      $authenticationCredentials
     */
    public function __construct($hostname, $authenticationType = self::AUTHENTICATION_PUBLIC_KEY, $authenticationUsername = null, $authenticationCredentials = '~/.ssh/id_rsa')
    {
        $this->hostname = $hostname;
        $this->authenticationUsername = $authenticationUsername;
        if (isset($this->authenticationUsername) === false) {
            $this->authenticationUsername = $this->getCurrentUsername();
        }

        switch ($authenticationType) {
            case self::AUTHENTICATION_PASSWORD:
                $authentication = $authenticationCredentials;
                break;
            case self::AUTHENTICATION_PUBLIC_KEY:
                $authentication = new RSA();
                $authentication->loadKey(file_get_contents(preg_replace('/^~/', $this->getUserDirectory(), $authenticationCredentials)));
                break;
            case self::AUTHENTICATION_SSH_AGENT:
                $authentication = new Agent();
                break;
            default:
                throw new UnexpectedValueException(sprintf('Invalid SSH authentication type: %s', $authenticationType));
        }

        $this->authenticationCredentials = $authentication;
    }

    /**
     * {@inheritdoc}
     */
    public function connect()
    {
        if ($this->isConnected()) {
            return true;
        }

        $this->connection = new SFTP($this->hostname);

        return $this->connection->login($this->authenticationUsername, $this->authenticationCredentials);
    }

    /**
     * {@inheritdoc}
     */
    public function disconnect()
    {
        if ($this->connection instanceof SFTP) {
            $this->connection->disconnect();

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isConnected()
    {
        if ($this->connection instanceof SFTP) {
            return $this->connection->isConnected();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isFile($remoteFilename)
    {
        if ($this->isConnected()) {
            return $this->connection->is_file($remoteFilename);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isLink($remoteTarget)
    {
        if ($this->isConnected()) {
            return $this->connection->is_link($remoteTarget);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isDirectory($remoteDirectory)
    {
        if ($this->isConnected()) {
            return $this->connection->is_dir($remoteDirectory);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function readLink($remoteTarget)
    {
        if ($this->isConnected()) {
            return $this->connection->readlink($remoteTarget);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function changeWorkingDirectory($remoteDirectory)
    {
        if ($this->isConnected()) {
            $this->executeCommand('cd', array($remoteDirectory));

            return $this->connection->chdir($remoteDirectory);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function executeCommand($command, array $arguments = array())
    {
        if ($this->isConnected()) {
            $this->connection->enableQuietMode();
            $this->connection->setTimeout(0);

            if (empty($arguments) === false) {
                $command = ProcessUtility::escapeArguments($arguments, $command);
            }

            if (isset($this->connection->server_channels[SFTP::CHANNEL_SHELL]) === false) {
                $this->connection->read($this->getShellPromptRegex(), SFTP::READ_REGEX);
            }

            $this->connection->write($command."\n");
            $output = $this->getFilteredOutput($this->connection->read($this->getShellPromptRegex(), SFTP::READ_REGEX), $command);

            $this->connection->write("echo $?\n");

            $exitCode = intval($this->getFilteredOutput($this->connection->read($this->getShellPromptRegex(), SFTP::READ_REGEX), 'echo $?'));
            $errorOutput = strval($this->connection->getStdError());

            $this->connection->disableQuietMode();

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
            return $this->connection->pwd();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getDirectoryContentsList($remoteDirectory)
    {
        if ($this->isConnected()) {
            $contentsList = array_values(array_diff($this->connection->nlist($remoteDirectory), array('.', '..')));
            sort($contentsList);

            return $contentsList;
        }

        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getContents($remoteFilename)
    {
        if ($this->isConnected()) {
            return $this->connection->get($remoteFilename);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getFile($remoteFilename, $localFilename)
    {
        if ($this->isConnected()) {
            return $this->connection->get($remoteFilename, $localFilename);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function createDirectory($remoteDirectory, $fileMode = 0770, $recursive = false)
    {
        if ($this->isConnected()) {
            return $this->connection->mkdir($remoteDirectory, $fileMode, $recursive);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function createFile($remoteFilename, $fileMode = 0770)
    {
        if ($this->isConnected()) {
            return ($this->connection->touch($remoteFilename) && $this->changePermissions($remoteFilename, $fileMode));
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function link($remoteSource, $remoteTarget)
    {
        if ($this->isConnected()) {
            return $this->connection->symlink($remoteSource, $remoteTarget);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function move($remoteSource, $remoteDestination)
    {
        if ($this->isConnected()) {
            return $this->connection->rename($remoteSource, $remoteDestination);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function copy($remoteSource, $remoteDestination)
    {
        if ($this->isConnected()) {
            $temporaryFile = tmpfile();

            if ($this->getFile($remoteSource, $temporaryFile) === false) {
                fclose($temporaryFile);

                return false;
            }

            rewind($temporaryFile);

            return $this->putContents($remoteDestination, $temporaryFile);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function changePermissions($remoteTarget, $fileMode, $recursive = false)
    {
        if ($this->isConnected()) {
            $result = $this->connection->chmod($fileMode, $remoteTarget, $recursive);

            return ($result !== false);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function putContents($destinationFilename, $data)
    {
        if ($this->isConnected()) {
            return $this->connection->put($destinationFilename, $data, SFTP::SOURCE_STRING);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function putFile($sourceFilename, $destinationFilename)
    {
        if ($this->isConnected()) {
            return $this->connection->put($destinationFilename, $sourceFilename, SFTP::SOURCE_LOCAL_FILE);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($remoteTarget, $recursive = false)
    {
        if ($this->isConnected()) {
            return $this->connection->delete($remoteTarget, $recursive);
        }

        return false;
    }

    /**
     * Returns the username of user executing the script.
     *
     * @return string
     */
    private function getCurrentUsername()
    {
        return $_SERVER['USER'];
    }

    /**
     * Returns the 'home' directory for the user.
     *
     * @return string|null
     */
    private function getUserDirectory()
    {
        $userDirectory = null;
        if (isset($_SERVER['HOME'])) {
            $userDirectory = $_SERVER['HOME'];
        } elseif (isset($_SERVER['USERPROFILE'])) {
            $userDirectory = $_SERVER['USERPROFILE'];
        }
        $userDirectory = realpath($userDirectory.'/../');
        $userDirectory .= '/'.$this->authenticationUsername;

        return $userDirectory;
    }

    /**
     * Returns the filtered output of the command.
     * Removes the command echo and shell prompt from the output.
     *
     * @param string $output
     * @param string $command
     *
     * @return string
     */
    private function getFilteredOutput($output, $command)
    {
        $output = str_replace(array("\r\n", "\r"), array("\n", ''), $output);

        $matches = array();
        if (preg_match($this->getOutputFilterRegex($command), $output, $matches) === 1) {
            $output = ltrim($matches[1]);
        }

        return $output;
    }

    /**
     * Returns the output filter regex to filter the output.
     *
     * @param string $command
     *
     * @return string
     */
    private function getOutputFilterRegex($command)
    {
        $commandCharacters = str_split(preg_quote($command, '/'));
        $commandCharacterRegexWhitespaceFunction = function ($value) {
            if ($value !== '\\') {
                $value .= '\s?';
            }

            return $value;
        };

        $commandCharacters = array_map($commandCharacterRegexWhitespaceFunction, $commandCharacters);

        return sprintf('/%s(.*)%s/s', implode('', $commandCharacters), substr($this->getShellPromptRegex(), 1, -1));
    }

    /**
     * Returns the regex matching the shell prompt.
     *
     * @return string
     */
    private function getShellPromptRegex()
    {
        return sprintf('/%s@.*[$|#]/', $this->authenticationUsername);
    }
}
