<?php

namespace Accompli;

/**
 * AccompliEvents.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
final class AccompliEvents
{
    /**
     * The CREATE_CONNECTION event is dispatched when a connection (connection adapter) needs to be created for a Host.
     *
     * The event listener receives an
     * Accompli\EventDispatcher\Event\HostEvent instance.
     *
     * @var string
     */
    const CREATE_CONNECTION = 'accompli.create_connection';

    /**
     * The DEPLOY_COMMAND_COMPLETE event is dispatched at the end of Accompli::deploy.
     *
     * The event listener recieves an
     * Symfony\Component\EventDispatcher\Event instance.
     *
     * @var string
     */
    const DEPLOY_COMMAND_COMPLETE = 'accompli.deploy_command_complete';

    /**
     * The DEPLOY_RELEASE event is dispatched when a Release is ready for deployment.
     *
     * The event listener receives an
     * Accompli\EventDispatcher\Event\DeployReleaseEvent instance.
     *
     * @var string
     */
    const DEPLOY_RELEASE = 'accompli.deploy_release';

    /**
     * The DEPLOY_RELEASE_COMPLETE event is dispatched when a Release is succesfully deployed.
     *
     * The event listener receives an
     * Accompli\EventDispatcher\Event\DeployReleaseEvent instance.
     *
     * @var string
     */
    const DEPLOY_RELEASE_COMPLETE = 'accompli.deploy_release_complete';

    /**
     * The DEPLOY_RELEASE_FAILED event is dispatched when deployment of a Release has failed.
     *
     * The event listener receives an
     * Accompli\EventDispatcher\Event\FailedEvent instance.
     *
     * @var string
     */
    const DEPLOY_RELEASE_FAILED = 'accompli.deploy_release_failed';

    /**
     * The GATHER_FACTS event is dispatched when information from a host is required.
     *
     * The event listener receives an
     * Accompli\EventDispatcher\Event\GatherFactsEvent instance.
     *
     * @var string
     */
    const GATHER_FACTS = 'accompli.gather_facts';

    /**
     * The GET_WORKSPACE event is dispatched when the Workspace instance from a host is required.
     *
     * The event listener receives an
     * Accompli\EventDispatcher\Event\WorkspaceEvent instance.
     *
     * @var string
     */
    const GET_WORKSPACE = 'accompli.get_workspace';

    /**
     * The INITIALIZE event is dispatched when the service container, configuration and event dispatcher are fully configured.
     *
     * The event listener recieves an
     * Symfony\Component\EventDispatcher\Event instance.
     *
     * @var string
     */
    const INITIALIZE = 'accompli.initialize';

    /**
     * The INSTALL_COMMAND_COMPLETE event is dispatched at the end of Accompli::install.
     *
     * The event listener recieves an
     * Symfony\Component\EventDispatcher\Event instance.
     *
     * @var string
     */
    const INSTALL_COMMAND_COMPLETE = 'accompli.install_command_complete';

    /**
     * The INSTALL_RELEASE event is dispatched when a Release requires installation.
     *
     * The event listener receives an
     * Accompli\EventDispatcher\Event\InstallReleaseEvent instance.
     *
     * @var string
     */
    const INSTALL_RELEASE = 'accompli.install_release';

    /**
     * The INSTALL_RELEASE_COMPLETE event is dispatched when a Release is successfully installed.
     *
     * The event listener receives an
     * Accompli\EventDispatcher\Event\InstallReleaseEvent instance.
     *
     * @var string
     */
    const INSTALL_RELEASE_COMPLETE = 'accompli.install_release_complete';

    /**
     * The INSTALL_RELEASE_FAILED event is dispatched when installation of a Release has failed.
     *
     * The event listener receives an
     * Accompli\EventDispatcher\Event\FailedEvent instance.
     *
     * @var string
     */
    const INSTALL_RELEASE_FAILED = 'accompli.install_release_failed';

    /**
     * The LOG event is dispatched whenever messages need to be logged to the logger service.
     *
     * The event listener receives an
     * Accompli\EventDispatcher\Event\LogEvent instance.
     *
     * @var string
     */
    const LOG = 'accompli.log';

    /**
     * The PREPARE_DEPLOY_RELEASE event is dispatched when a Release is prepared for deployment.
     *
     * The event listener receives an
     * Accompli\EventDispatcher\Event\PrepareDeployReleaseEvent instance.
     *
     * @var string
     */
    const PREPARE_DEPLOY_RELEASE = 'accompli.prepare_deploy_release';

    /**
     * The PREPARE_RELEASE event is dispatched when a Release is being prepared.
     *
     * The event listener receives an
     * Accompli\EventDispatcher\Event\PrepareReleaseEvent instance.
     *
     * @var string
     */
    const PREPARE_RELEASE = 'accompli.prepare_release';

    /**
     * The PREPARE_WORKSPACE event is dispatched when a server or local path is being prepared for deployments.
     *
     * The event listener receives an
     * Accompli\EventDispatcher\Event\WorkspaceEvent instance.
     *
     * @var string
     */
    const PREPARE_WORKSPACE = 'accompli.prepare_workspace';

    /**
     * The ROLLBACK_RELEASE event is dispatched when a previous Release is being deployed.
     *
     * The event listener receives an
     * Accompli\EventDispatcher\Event\DeployReleaseEvent instance.
     *
     * @var string
     */
    const ROLLBACK_RELEASE = 'accompli.rollback_release';

    /**
     * The ROLLBACK_RELEASE_COMPLETE event is dispatched when a previous Release is successfully deployed.
     *
     * The event listener receives an
     * Accompli\EventDispatcher\Event\DeployReleaseEvent instance.
     *
     * @var string
     */
    const ROLLBACK_RELEASE_COMPLETE = 'accompli.rollback_release_complete';

    /**
     * The ROLLBACK_RELEASE_FAILED event is dispatched when deployment of a previous Release has failed.
     *
     * The event listener receives an
     * Accompli\EventDispatcher\Event\FailedEvent instance.
     *
     * @var string
     */
    const ROLLBACK_RELEASE_FAILED = 'accompli.rollback_release_failed';

    /**
     * Returns an array with all the event names (constants).
     *
     * @return array
     */
    public static function getEventNames()
    {
        return array(
            self::CREATE_CONNECTION,
            self::DEPLOY_COMMAND_COMPLETE,
            self::DEPLOY_RELEASE,
            self::DEPLOY_RELEASE_COMPLETE,
            self::DEPLOY_RELEASE_FAILED,
            self::GATHER_FACTS,
            self::GET_WORKSPACE,
            self::INITIALIZE,
            self::INSTALL_COMMAND_COMPLETE,
            self::INSTALL_RELEASE,
            self::INSTALL_RELEASE_COMPLETE,
            self::INSTALL_RELEASE_FAILED,
            self::LOG,
            self::PREPARE_DEPLOY_RELEASE,
            self::PREPARE_RELEASE,
            self::PREPARE_WORKSPACE,
            self::ROLLBACK_RELEASE,
            self::ROLLBACK_RELEASE_COMPLETE,
            self::ROLLBACK_RELEASE_FAILED,
        );
    }
}
