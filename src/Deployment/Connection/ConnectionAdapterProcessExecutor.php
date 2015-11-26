<?php

namespace Accompli\Deployment\Connection;

use Accompli\Chrono\Process\ProcessExecutorInterface;

/**
 * ConnectionAdapterProcessExecutor.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class ConnectionAdapterProcessExecutor implements ProcessExecutorInterface
{
    /**
     * The connection adapter instance.
     *
     * @var ConnectionAdapterInterface
     */
    private $connectionAdapter;

    /**
     * Constructs a new ConnectionAdapterProcessExecutor.
     *
     * @param ConnectionAdapterInterface $connectionAdapter
     */
    public function __construct(ConnectionAdapterInterface $connectionAdapter)
    {
        $this->connectionAdapter = $connectionAdapter;
    }

    /**
     * {@inheritdoc}
     */
    public function isDirectory($path)
    {
        return $this->connectionAdapter->isDirectory($path);
    }

    /**
     * {@inheritdoc}
     */
    public function execute($command, $workingDirectory = null)
    {
        $previousWorkingDirectory = null;
        if ($workingDirectory !== null) {
            $previousWorkingDirectory = $this->connectionAdapter->getWorkingDirectory();

            $this->connectionAdapter->changeWorkingDirectory($workingDirectory);
        }

        $result = $this->connectionAdapter->executeCommand($command);

        if ($previousWorkingDirectory !== null) {
            $this->connectionAdapter->changeWorkingDirectory($previousWorkingDirectory);
        }

        return $result;
    }
}
