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
     * The ProcessExecutionResult instance of the last executed command.
     *
     * @var ProcessExecutionResult
     */
    private $lastProcessExecutionResult;

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
    public function execute($command, $workingDirectory = null, array $environmentVariables = null)
    {
        $previousWorkingDirectory = null;
        if ($workingDirectory !== null) {
            $previousWorkingDirectory = $this->connectionAdapter->getWorkingDirectory();

            $this->connectionAdapter->changeWorkingDirectory($workingDirectory);
        }

        $environment = '';
        if (isset($environmentVariables)) {
            foreach ($environmentVariables as $environmentVariableName => $environmentVariableValue) {
                $environment .= sprintf('%s=%s ', $environmentVariableName, $environmentVariableValue);
            }
        }

        $this->lastProcessExecutionResult = $this->connectionAdapter->executeCommand($environment.$command);

        if ($previousWorkingDirectory !== null) {
            $this->connectionAdapter->changeWorkingDirectory($previousWorkingDirectory);
        }

        return $this->lastProcessExecutionResult;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastProcessExecutionResult()
    {
        return $this->lastProcessExecutionResult;
    }
}
