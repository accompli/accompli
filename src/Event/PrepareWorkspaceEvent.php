<?php

namespace Accompli\Event;

use Accompli\Deployment\Host;
use Accompli\Deployment\Workspace;
use Symfony\Component\EventDispatcher\Event;

/**
 * PrepareWorkspaceEvent
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 * @package Accompli\Event
 */
class PrepareWorkspaceEvent extends Event
{
    /**
     * The Host instance
     *
     * @access private
     * @var Host
     **/
    private $host;

    /**
     * The Workspace instance
     *
     * @access private
     * @var Workspace
     **/
    private $workspace;

    /**
     * __construct
     *
     * Constructs a new PrepareWorkspaceEvent
     *
     * @access public
     * @param  Host $host
     * @return null
     **/
    public function __construct(Host $host)
    {
        $this->host = $host;
    }

    /**
     * setWorkspace
     *
     * Sets a Workspace instance
     *
     * @access public
     * @param  Workspace $workspace
     * @return null
     **/
    public function setWorkspace(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    /**
     * getWorkspace
     *
     * Returns the Workspace instance
     *
     * @access public
     * @return Workspace
     **/
    public function getWorkspace()
    {
        return $this->workspace;
    }
}
