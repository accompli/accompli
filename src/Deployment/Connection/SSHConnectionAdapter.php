<?php

namespace Accompli\Deployment\Connection;

use RuntimeException;
use Ssh\Authentication;
use Ssh\Authentication\Agent;
use Ssh\Authentication\PublicKeyFile;
use Ssh\Configuration;
use Ssh\Session;
use UnexpectedValueException;

/**
 * SSHConnectionAdapter.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class SSHConnectionAdapter implements ConnectionAdapterInterface
{
    /**
     * The SSH configuration instance.
     *
     * @var Configuration
     */
    private $configuration;

    /**
     * The SSH authentication instance.
     *
     * @var Authentication
     **/
    private $authentication;

    /**
     * The SSH Session instance.
     *
     * @var Session
     */
    private $connection;

    /**
     * Constructs a new SSHConnectionAdapter instance.
     *
     * @param string      $hostname
     * @param string      $authenticationType
     * @param string|null $authenticationUsername
     * @param string      $authenticationPublicKeyFile
     * @param string      $authenticationPrivateKeyFile
     */
    public function __construct($hostname, $authenticationType, $authenticationUsername = null, $authenticationPublicKeyFile = '~/.ssh/id_rsa.pub', $authenticationPrivateKeyFile = '~/.ssh/id_rsa')
    {
        $this->configuration = new Configuration($hostname);

        switch ($authenticationType) {
            case 'publickey':
                $authentication = new PublicKeyFile($authenticationUsername, $authenticationPublicKeyFile, $authenticationPrivateKeyFile);
                break;
            case 'agent':
                $authentication = new Agent($authenticationUsername);
                break;
            default:
                throw new UnexpectedValueException(sprintf('Invalid SSH authentication type: %s', $authenticationType));
        }

        $this->authentication = $authentication;
    }

    /**
     * {@inheritDoc}
     */
    public function connect()
    {
        $this->connection = new Session($this->configuration, $this->authentication);

        try {
            // Retrieve the Exec subsystem to force the connection
            $this->connection->getExec();

            return true;
        } catch (RuntimeException $exception) {
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function executeCommand($command)
    {
        $exec = $this->connection->getExec();

        return $exec->run($command);
    }

    /**
     * {@inheritDoc}
     */
    public function getContents($filename)
    {
        $sFtp = $this->connection->getSftp();

        return $sFtp->read($filename);
    }

    /**
     * {@inheritDoc}
     */
    public function putContents($destinationFilename, $data)
    {
        $sFtp = $this->connection->getSftp();
        $result = $sFtp->write($destinationFilename, $data);

        return ($result !== false);
    }

    /**
     * {@inheritDoc}
     */
    public function putFile($sourceFilename, $destinationFilename)
    {
        $sFtp = $this->connection->getSftp();
        $result = $sFtp->send($sourceFilename, $destinationFilename);

        return ($result !== false);
    }
}
