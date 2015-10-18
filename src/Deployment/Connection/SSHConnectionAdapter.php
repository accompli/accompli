<?php

namespace Accompli\Deployment\Connection;

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
            $this->authenticationUsername = get_current_user();
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
    public function executeCommand($command)
    {
        if ($this->connection instanceof SFTP && $this->connection->isConnected() === true) {
            return $this->connection->exec($command);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents($filename)
    {
        if ($this->connection instanceof SFTP && $this->connection->isConnected() === true) {
            return $this->connection->get($filename);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function putContents($destinationFilename, $data)
    {
        if ($this->connection instanceof SFTP && $this->connection->isConnected() === true) {
            return $this->connection->put($destinationFilename, $data, SFTP::SOURCE_STRING);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getFile($sourceFilename, $destinationFilename)
    {
        if ($this->connection instanceof SFTP && $this->connection->isConnected() === true) {
            return $this->connection->get($sourceFilename, $destinationFilename);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function putFile($sourceFilename, $destinationFilename)
    {
        if ($this->connection instanceof SFTP && $this->connection->isConnected() === true) {
            return $this->connection->put($destinationFilename, $sourceFilename, SFTP::SOURCE_LOCAL_FILE);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function linkFile($remoteTarget, $remoteLink)
    {
        if ($this->connection instanceof SFTP && $this->connection->isConnected() === true) {
            return $this->connection->symlink($remoteTarget, $remoteLink);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function renameFile($remoteSource, $remoteDestination)
    {
        if ($this->connection instanceof SFTP && $this->connection->isConnected() === true) {
            return $this->connection->rename($remoteSource, $remoteDestination);
        }

        return false;
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
}
