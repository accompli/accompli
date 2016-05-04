<?php

namespace Accompli\Exception;

use Accompli\Chrono\Process\ProcessExecutionResult;
use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * TaskCommandExecutionException is thrown when an executed command returns an unsuccessful status.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class TaskCommandExecutionException extends TaskRuntimeException
{
    /**
     * The ProcessExecutionResult instance from the executed command.
     *
     * @var string
     */
    private $processExecutionResult;

    /**
     * Constructs a new TaskCommandExecutionException instance.
     *
     * @param string                   $message
     * @param ProcessExecutionResult   $result
     * @param EventSubscriberInterface $task
     * @param int                      $code
     * @param Exception                $previous
     */
    public function __construct($message = '', ProcessExecutionResult $result = null, EventSubscriberInterface $task = null, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $task, $code, $previous);

        $this->processExecutionResult = $result;
    }

    /**
     * Returns the ProcessExecutionResult instance.
     *
     * @return string
     */
    public function getProcessExecutionResult()
    {
        return $this->processExecutionResult;
    }
}
