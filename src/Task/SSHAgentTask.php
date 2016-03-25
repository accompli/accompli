<?php

namespace Accompli\Task;

use Accompli\AccompliEvents;
use Accompli\Deployment\Connection\ConnectionAdapterInterface;
use Accompli\Deployment\Host;
use Accompli\Deployment\Workspace;
use Accompli\EventDispatcher\Event\LogEvent;
use Accompli\EventDispatcher\Event\WorkspaceEvent;
use Accompli\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\Event;

/**
 * SSHAgentTask.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class SSHAgentTask extends AbstractConnectedTask
{
    /**
     * The array with SSH keys to be added to the SSH agent.
     *
     * @var array
     */
    private $keys;

    /**
     * The Host instance.
     *
     * @var Host
     */
    private $host;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            AccompliEvents::PREPARE_WORKSPACE => array(
                array('onPrepareWorkspaceInitializeSSHAgent', 0),
            ),
            AccompliEvents::INSTALL_RELEASE_COMPLETE => array(
                array('onInstallReleaseCompleteOrFailedShutdownSSHAgent', 0),
            ),
            AccompliEvents::INSTALL_RELEASE_FAILED => array(
                array('onInstallReleaseCompleteOrFailedShutdownSSHAgent', 0),
            ),
        );
    }

    /**
     * Constructs a new SSHAgentTask instance.
     *
     * @param array $keys
     */
    public function __construct(array $keys = array())
    {
        $this->keys = $keys;
    }

    /**
     * Initializes the SSH agent and adds the configured keys.
     *
     * @param WorkspaceEvent           $event
     * @param string                   $eventName
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function onPrepareWorkspaceInitializeSSHAgent(WorkspaceEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        $this->host = $event->getHost();

        $connection = $this->ensureConnection($this->host);

        $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::NOTICE, 'Initializing SSH agent...', $eventName, $this, array('event.task.action' => TaskInterface::ACTION_IN_PROGRESS)));

        $result = $connection->executeCommand('eval $(ssh-agent)');
        if ($result->isSuccessful()) {
            $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::NOTICE, 'Initialized SSH agent. {output}', $eventName, $this, array('output' => trim($result->getOutput()), 'event.task.action' => TaskInterface::ACTION_COMPLETED, 'output.resetLine' => true)));

            foreach ($this->keys as $key) {
                $this->addKeyToSSHAgent($event->getWorkspace(), $connection, $key, $eventName, $eventDispatcher);
            }
        } else {
            $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::WARNING, 'Failed initializing SSH agent.', $eventName, $this, array('event.task.action' => TaskInterface::ACTION_FAILED, 'output.resetLine' => true)));
        }
    }

    /**
     * Terminates SSH agent after release installation is successful or has failed.
     *
     * @param Event                    $event
     * @param string                   $eventName
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function onInstallReleaseCompleteOrFailedShutdownSSHAgent(Event $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        if ($this->host instanceof Host) {
            $connection = $this->ensureConnection($this->host);

            $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::NOTICE, 'Terminating SSH agent...', $eventName, $this, array('event.task.action' => TaskInterface::ACTION_IN_PROGRESS)));

            $result = $connection->executeCommand('eval $(ssh-agent -k)');
            if ($result->isSuccessful()) {
                $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::NOTICE, 'Terminated SSH agent.', $eventName, $this, array('event.task.action' => TaskInterface::ACTION_COMPLETED, 'output.resetLine' => true)));
            } else {
                $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::WARNING, 'Failed terminating SSH agent.', $eventName, $this, array('event.task.action' => TaskInterface::ACTION_FAILED, 'output.resetLine' => true)));
            }
        }
    }

    /**
     * Adds an SSH key to the initialized SSH agent.
     *
     * @param Workspace                  $workspace
     * @param ConnectionAdapterInterface $connection
     * @param string                     $key
     * @param string                     $eventName
     * @param EventDispatcherInterface   $eventDispatcher
     */
    private function addKeyToSSHAgent(Workspace $workspace, ConnectionAdapterInterface $connection, $key, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, 'Adding key to SSH agent...', $eventName, $this, array('event.task.action' => TaskInterface::ACTION_IN_PROGRESS)));

        $keyFile = $workspace->getHost()->getPath().'/tmp.key';
        if ($connection->createFile($keyFile, 0700) && $connection->putContents($keyFile, $key)) {
            $result = $connection->executeCommand('ssh-add', array($keyFile));
            if ($result->isSuccessful()) {
                $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, 'Added key to SSH agent.', $eventName, $this, array('event.task.action' => TaskInterface::ACTION_COMPLETED, 'output.resetLine' => true)));
            }

            $connection->delete($keyFile);

            if ($result->isSuccessful()) {
                return;
            }
        }

        $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::INFO, 'Failed adding key to SSH agent.', $eventName, $this, array('event.task.action' => TaskInterface::ACTION_FAILED, 'output.resetLine' => true)));
    }
}
