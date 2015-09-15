<?php

namespace Accompli\Event;

use Accompli\Deployment\Host;
use Accompli\Deployment\Workspace;
use Symfony\Component\EventDispatcher\Event;

/**
 * PrepareWorkspaceEvent.
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 */
class PrepareWorkspaceEvent extends Event
{
    /**
     * The Host instance.
     *
     * @var Host
     */
    private $host;

    /**
     * The Workspace instance.
     *
     * @var Workspace
     */
    private $workspace;

    /**
     * Constructs a new PrepareWorkspaceEvent
     *
     * @param Host $host
     */
    public function __construct(Host $host)
    {
        $this->host = $host;
    }

    /**
     * Sets a Workspace instance
     *
     * @param Workspace $workspace
     */
    public function setWorkspace(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    /**
     * Returns the Workspace instance
     *
     * @return Workspace|null
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }
}
