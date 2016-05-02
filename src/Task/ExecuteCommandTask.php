<?php

namespace Accompli\Task;

use Accompli\AccompliEvents;
use Accompli\EventDispatcher\Event\LogEvent;
use Accompli\EventDispatcher\Event\ReleaseEvent;
use Accompli\EventDispatcher\EventDispatcherInterface;
use Accompli\Exception\TaskCommandExecutionException;
use Accompli\Exception\TaskRuntimeException;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\Event;

/**
 * ExecuteCommandTask.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class ExecuteCommandTask extends AbstractConnectedTask
{
    /**
     * The event names for the task to be executed on.
     *
     * @var array
     */
    private $events;

    /**
     * The path to the command.
     *
     * @var string
     */
    private $command;

    /**
     * The arguments to be passed to the command.
     *
     * @var array
     */
    private $arguments;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            AccompliEvents::PREPARE_RELEASE => array(
                array('onEvent', 0),
            ),
            AccompliEvents::INSTALL_RELEASE => array(
                array('onEvent', 0),
            ),
            AccompliEvents::DEPLOY_RELEASE => array(
                array('onEvent', 10),
            ),
            AccompliEvents::ROLLBACK_RELEASE => array(
                array('onEvent', 10),
            ),
        );
    }

    /**
     * Constructs a new ExecuteCommandTask.
     *
     * @param array  $events
     * @param string $command
     * @param array  $arguments
     */
    public function __construct(array $events, $command, array $arguments = array())
    {
        $this->events = $events;
        $this->command = $command;
        $this->arguments = $arguments;
    }

    /**
     * Executes the configured command.
     *
     * @param Event                    $event
     * @param string                   $eventName
     * @param EventDispatcherInterface $eventDispatcher
     *
     * @throws TaskRuntimeException when execution of the command has failed.
     */
    public function onEvent(Event $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        if (in_array($eventName, $this->events)) {
            if ($event instanceof ReleaseEvent) {
                $release = $event->getRelease();

                $host = $release->getWorkspace()->getHost();
                $path = $release->getPath();
            } else {
                $host = $event->getWorkspace()->getHost();
                $path = $host->getPath();
            }

            $connection = $this->ensureConnection($host);

            $currentWorkingDirectory = $connection->getWorkingDirectory();

            $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::NOTICE, 'Executing command "{command}".', $eventName, $this, array('command' => $this->command, 'event.task.action' => TaskInterface::ACTION_IN_PROGRESS)));

            $connection->changeWorkingDirectory($path);
            $result = $connection->executeCommand($this->command, $this->arguments);
            $connection->changeWorkingDirectory($currentWorkingDirectory);

            if ($result->isSuccessful()) {
                $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::NOTICE, 'Executed command "{command}".', $eventName, $this, array('command' => $this->command, 'event.task.action' => TaskInterface::ACTION_COMPLETED, 'output.resetLine' => true)));
                $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::DEBUG, "{separator} Command output:{separator}\n{command.result}{separator}", $eventName, $this, array('command.result' => $result->getOutput(), 'separator' => "\n=================\n")));
            } else {
                throw new TaskCommandExecutionException(sprintf('Failed executing command "%s".', $this->command), $result, $this);
            }
        }
    }
}
