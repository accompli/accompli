<?php

namespace Accompli\EventDispatcher\Event;

use Accompli\Deployment\Workspace;

/**
 * PrepareWorkspaceEvent.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class PrepareWorkspaceEvent extends HostEvent
{
    /**
     * The Workspace instance.
     *
     * @var Workspace
     */
    private $workspace;

    /**
     * Sets a Workspace instance.
     *
     * @param Workspace $workspace
     */
    public function setWorkspace(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    /**
     * Returns the Workspace instance.
     *
     * @return Workspace|null
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }
}
