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
    public function __construct($hostname, $authenticationType = 'publickey', $authenticationUsername = null, $authenticationCredentials = '~/.ssh/id_rsa')
    {
        $this->hostname = $hostname;
        $this->authenticationUsername = $authenticationUsername;
        if (isset($this->authenticationUsername) === false) {
            $this->authenticationUsername = get_current_user();
        }

        switch ($authenticationType) {
            case 'password':
                $authentication = $authenticationCredentials;
                break;
            case 'publickey':
                $authentication = new RSA();
                $authentication->loadKey(preg_replace('/^~/', $this->getUserDirectory(), $authenticationCredentials));
                break;
            case 'agent':
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
        return $this->connection->disconnect();
    }

    /**
     * {@inheritdoc}
     */
    public function executeCommand($command)
    {
        return $this->connection->exec($command);
    }

    /**
     * {@inheritdoc}
     */
    public function getContents($filename)
    {
        return $this->connection->get($filename);
    }

    /**
     * {@inheritdoc}
     */
    public function putContents($destinationFilename, $data)
    {
        return $this->connection->put($destinationFilename, $data, SFTP::SOURCE_STRING);
    }

    /**
     * {@inheritdoc}
     */
    public function getFile($sourceFilename, $destinationFilename)
    {
        return $this->connection->get($sourceFilename, $destinationFilename);
    }

    /**
     * {@inheritdoc}
     */
    public function putFile($sourceFilename, $destinationFilename)
    {
        return $this->connection->put($destinationFilename, $sourceFilename, SFTP::SOURCE_LOCAL_FILE);
    }

    /**
     * {@inheritdoc}
     */
    public function linkFile($remoteTarget, $remoteLink)
    {
        return $this->connection->symlink($remoteTarget, $remoteLink);
    }

    /**
     * {@inheritdoc}
     */
    public function renameFile($remoteSource, $remoteDestination)
    {
        return $this->connection->rename($remoteSource, $remoteDestination);
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
