<?php

namespace Accompli\Task;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * TaskInterface.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
interface TaskInterface extends EventSubscriberInterface
{
    /**
     * Log context indicator that an action of a task is "in progress".
     *
     * @var string
     */
    const ACTION_IN_PROGRESS = 'in_progress';

    /**
     * Log context indicator that an action of a task is "completed".
     *
     * @var string
     */
    const ACTION_COMPLETED = 'completed';

    /**
     * Log context indicator that an action of a task has "failed".
     *
     * @var string
     */
    const ACTION_FAILED = 'failed';
}
