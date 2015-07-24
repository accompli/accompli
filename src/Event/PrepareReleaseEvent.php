<?php

namespace Accompli\Event;

use Accompli\Deployment\Workspace;
use Accompli\Release;
use Symfony\Component\EventDispatcher\Event;

/**
 * PrepareReleaseEvent
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 * @package Accompli\Event
 **/
class PrepareReleaseEvent extends Event
{
    /**
     * The Workspace instance
     *
     * @access private
     * @var Workspace
     **/
    private $workspace;

    /**
     * The Release instance
     *
     * @access protected
     * @var Release
     **/
    protected $release;

    /**
     * __construct
     *
     * Constructs a new PrepareReleaseEvent
     *
     * @access public
     * @param  Workspace $workspace
     * @return null
     **/
    public function __construct(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    /**
     * setRelease
     *
     * Sets a Release instance
     *
     * @access public
     * @param  Release $release
     * @return null
     **/
    public function setRelease(Release $release)
    {
        $this->release = $release;
    }

    /**
     * getRelease
     *
     * Returns the Release instance
     *
     * @access public
     * @return Release
     **/
    public function getRelease()
    {
        return $this->release;
    }
}
