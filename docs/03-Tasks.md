# Tasks

A task consists of one or more installation and/or deployment actions. They execute the actions by listening to events dispatched by the [Symfony Event Dispatcher][link-symfony-event-dispatcher] service.

## Available tasks

The following tasks are included in Accompli:

### Tasks for installing a release

* [ComposerInstallTask](tasks/ComposerInstallTask.md)
* [CreateWorkspaceTask](tasks/CreateWorkspaceTask.md)
* [ExecuteCommandTask](tasks/ExecuteCommandTask.md)
* [RepositoryCheckoutTask](tasks/RepositoryCheckoutTask.md)
* [SSHAgentTask](tasks/SSHAgentTask.md)
* [YamlConfigurationTask](tasks/YamlConfigurationTask.md)

### Tasks for deploying a release

* [DeployReleaseTask](tasks/DeployReleaseTask.md)
* [ExecuteCommandTask](tasks/ExecuteCommandTask.md)
* [MaintenanceModeTask](tasks/MaintenanceModeTask.md)

## Creating a task

A task can be either an Event Listener or an Event Subscriber. For reusability it's easier for users to configure a task that is self-aware of the events it needs to listen to than to configure each event listener separately.

For more information on how to create an Event Subscriber, see the [Symfony documentation][link-symfony-event-dispatcher-event-subscriber].

### Usage of connection adapters

Due to the platform independent nature of Accompli and the ability to remotely install projects, all task should execute through a connection adapter 'middleman'. The connection adapter is retrieved from a Host object which is available either through an Event object or Workspace object (depending on the dispatched Event object).

The easiest way to ensure you have an active (connected) connection adapter is to extend from the [AbstractConnectedTask][link-accompli-abstract-connected-task] and retrieve the connection adapter by calling `ensureConnection`.

```php
<?php

namespace Accompli\Task;

/**
 * ExampleTask does something with a connection.
 */
class ExampleTask extends AbstractConnectedTask
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            AccompliEvents::PREPARE_WORKSPACE => array(
                array('onPrepareWorkspaceDoSomething', 0),
            ),
        );
    }

    /**
     * Do something by using the connection adapter.
     *
     * @param WorkspaceEvent $event
     */
    public function onPrepareWorkspaceDoSomething(WorkspaceEvent $event)
    {
        $connection = $this->ensureConnection($event->getHost());

        // Do something
    }
}

```

### Dispatched Accompli events

The amount of events dispatched depends on the configured deployment strategy. A typical deployment strategy should at least dispatch the events described below.

#### Events dispatched during installation of a release

1. AccompliEvents::CREATE_CONNECTION
2. AccompliEvents::PREPARE_WORKSPACE
3. AccompliEvents::PREPARE_RELEASE
4. AccompliEvents::INSTALL_RELEASE
5. AccompliEvents::INSTALL_RELEASE_COMPLETE (dispatched only when the installation of a release was completed successfully)
6. AccompliEvents::INSTALL_RELEASE_FAILED (dispatched only when eg. a certain task fails)

#### Events dispatched during deployment of a release

The deployment of a release has two scenario's:
* Deployment of a new release.
* Rollback to an old release.

Some dispatched events differ per scenario.

##### Deployment of a new release

1. AccompliEvents::CREATE_CONNECTION
2. AccompliEvents::GET_WORKSPACE
3. AccompliEvents::PREPARE_DEPLOY_RELEASE
4. AccompliEvents::DEPLOY_RELEASE
5. AccompliEvents::DEPLOY_RELEASE_COMPLETE (dispatched only when the deployment of a release was completed successfully)
6. AccompliEvents::DEPLOY_RELEASE_FAILED  (dispatched only when eg. a certain task fails)

##### Rollback deployment to an old release

1. AccompliEvents::CREATE_CONNECTION
2. AccompliEvents::GET_WORKSPACE
3. AccompliEvents::PREPARE_DEPLOY_RELEASE
4. AccompliEvents::ROLLBACK_RELEASE
5. AccompliEvents::ROLLBACK_RELEASE_COMPLETE (dispatched only when the deployment of a release was completed successfully)
6. AccompliEvents::ROLLBACK_RELEASE_FAILED (dispatched only when eg. a certain task fails)

For more in-depth information on the dispatched events, please see the [AccompliEvents][link-accompli-events-class] class.

[link-symfony-event-dispatcher]: http://symfony.com/doc/current/components/event_dispatcher/introduction.html
[link-symfony-event-dispatcher-event-subscriber]: http://symfony.com/doc/current/components/event_dispatcher/introduction.html#using-event-subscribers
[link-accompli-abstract-connected-task]: ../src/Task/AbstractConnectedTask
[link-accompli-events-class]: ../src/AccompliEvents.php
