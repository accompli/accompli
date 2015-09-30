<?php

namespace Accompli\Deployment\Connection;

use RuntimeException;
use Ssh\Authentication\Agent;
use Ssh\Configuration;
use Ssh\Session;

/**
 * SSHConnectionAdapter
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class SSHConnectionAdapter implements ConnectionAdapterInterface
{
    /**
     * The SSH Session instance.
     *
     * @var Session
     */
    private $connection;

    /**
     * {@inheritDoc}
     */
    public function connect()
    {
        // @todo Add some way to add configuration options...

        $host = "localhost";
        $configuration = new Configuration($host);
        $authentication = new Agent("username"); // Use username executing the accompli command

        $this->connection = new Session($configuration);

        try {
            // Retrieve the Exec subsystem to force the connection
            $this->connection->getExec();

            return true;
        } catch (RuntimeException $exception) { }

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
