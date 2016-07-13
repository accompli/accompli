<?php

namespace Accompli\Exception;

use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * TaskRuntimeException.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class TaskRuntimeException extends RuntimeException
{
    /**
     * The running task when the exception occurred.
     *
     * @var EventSubscriberInterface
     */
    private $task;

    /**
     * Constructs a new TaskRuntimeException instance.
     *
     * @param string                        $message
     * @param EventSubscriberInterface|null $task
     * @param int                           $code
     * @param Exception|null                $previous
     */
    public function __construct($message = '', EventSubscriberInterface $task = null, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->task = $task;
    }

    /**
     * Returns the task instance.
     *
     * @return EventSubscriberInterface
     */
    public function getTask()
    {
        return $this->task;
    }
}
