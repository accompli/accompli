<?php

namespace Accompli;

/**
 * AccompliEvents
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 * @package Accompli
 **/
final class AccompliEvents
{
    /**
     * The DEPLOY_DEPLOYMENT event is dispatched when a Release is ready for deployment.
     *
     * The event listener receives an
     * Accompli\Event\DeployDeploymentEvent instance.
     *
     * @var string
     **/
    const DEPLOY_DEPLOYMENT = "accompli.deploy_deployment";

    /**
     * The DEPLOYMENT_COMPLETE event is dispatched when a Release succesfully deployed.
     *
     * The event listener receives an
     * Accompli\Event\DeploymentCompleteEvent instance.
     *
     * @var string
     **/
    const DEPLOYMENT_COMPLETE = "accompli.deployment_complete";

    /**
     * The INSTALL_RELEASE event is dispatched when a Release requires installation.
     *
     * The event listener receives an
     * Accompli\Event\InstallReleaseEvent instance.
     *
     * @var string
     **/
    const INSTALL_RELEASE = "accompli.install_release";

    /**
     * The PREPARE_DEPLOYMENT event is dispatched when a Release is prepared for deployment.
     *
     * The event listener receives an
     * Accompli\Event\PrepareDeploymentEvent instance.
     *
     * @var string
     **/
    const PREPARE_DEPLOYMENT = "accompli.prepare_deployment";

    /**
     * The PREPARE_RELEASE event is dispatched when a Release is being prepared.
     *
     * The event listener receives an
     * Accompli\Event\PrepareReleaseEvent instance.
     *
     * @var string
     **/
    const PREPARE_RELEASE = "accompli.prepare_release";

    /**
     * The PREPARE_SERVER event is dispatched when a server is being prepared for deployments.
     *
     * The event listener receives an
     * Accompli\Event\PrepareServerEvent instance.
     *
     * @var string
     **/
    const PREPARE_SERVER = "accompli.prepare_server";

    /**
     * The ROLLBACK_DEPLOYMENT event is dispatched when a previous Release is being deployed.
     *
     * The event listener receives an
     * Accompli\Event\RollbackDeploymentEvent instance.
     *
     * @var string
     **/
    const ROLLBACK_DEPLOYMENT = "accompli.rollback_deployment";

    /**
     * The ROLLBACK_DEPLOYMENT_COMPLETE event is dispatched when a previous Release is successfully deployed.
     *
     * The event listener receives an
     * Accompli\Event\RollbackDeploymentCompleteEvent instance.
     *
     * @var string
     **/
    const ROLLBACK_DEPLOYMENT_COMPLETE = "accompli.rollback_deployment_complete";
}
