<?php

namespace Accompli\Deployment\Adapter;

/**
 * DeploymentAdapterInterface.
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 */
interface DeploymentAdapterInterface
{
    /**
     * Remote deployment type constant.
     *
     * @var string
     */
    const DEPLOYMENT_TYPE_REMOTE = 'deployment_remote';

    /**
     * Local deployment type constant.
     *
     * @var string
     */
    const DEPLOYMENT_TYPE_LOCAL = 'deployment_local';

    /**
     * Command Line Interface deployment method constant.
     *
     * @var string
     */
    const DEPLOYMENT_METHOD_CLI = 'cli_deployment';

    /**
     * Transfer deployment method constant.
     *
     * @var string
     */
    const DEPLOYMENT_METHOD_TRANSFER = 'transfer_deployment';

    /**
     * Returns the deployment type
     *
     * @return string
     */
    public function getDeploymentType();

    /**
     * Returns the deployment method
     *
     * @return string
     */
    public function getDeploymentMethod();

    /**
     * Connects the adapter
     */
    public function connect();

    /**
     * Executes a command
     *
     * @param string $command
     */
    public function executeCommand($command);

    /**
     * Uploads a file to a remote file
     *
     * @param string $localFilename
     * @param string $remoteFilename
     */
    public function putFile($localFilename, $remoteFilename);

    /**
     * Uploads $contents to a remote file
     *
     * @param string $remoteFilename
     * @param mixed  $data
     */
    public function putContents($remoteFilename, $data);

    /**
     * Returns the contents of a remote file
     *
     * @param string $remoteFilename
     *
     * @return mixed
     */
    public function getContents($remoteFilename);
}
