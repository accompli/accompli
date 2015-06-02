<?php

namespace Accompli\Deployment\Adapter;

/**
 * DeploymentAdapterInterface
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 * @package Accompli\Deployment\Adapter
 **/
interface DeploymentAdapterInterface
{
    /**
     * Remote deployment type constant
     *
     * @var string
     **/
    const DEPLOYMENT_TYPE_REMOTE = "deployment_remote";

    /**
     * Local deployment type constant
     *
     * @var string
     **/
    const DEPLOYMENT_TYPE_LOCAL = "deployent_local";

    /**
     * Command Line Interface deployment method constant
     *
     * @var string
     **/
    const DEPLOYMENT_METHOD_CLI = "cli_deployment";

    /**
     * Transfer deployment method constant
     *
     * @var string
     **/
    const DEPLOYMENT_METHOD_TRANSFER = "transfer_deployment";

    /**
     * getDeploymentType
     *
     * Returns the deployment type
     *
     * @access public
     * @return string
     **/
    public function getDeploymentType();

    /**
     * getDeploymentMethod
     *
     * Returns the deployment method
     *
     * @access public
     * @return string
     **/
    public function getDeploymentMethod();

    /**
     * connect
     *
     * Connects the adapter
     *
     * @access public
     * @return null
     **/
    public function connect();

    /**
     * executeCommand
     *
     * Executes a command
     *
     * @access public
     * @param  string $command
     * @return null
     **/
    public function executeCommand($command);
}
