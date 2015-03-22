<?php

namespace Accompli\Event;

use Accompli\Release;

/**
 * PrepareReleaseEvent
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 * @package Accompli\Event
 **/
class PrepareReleaseEvent extends AbstractEvent
{
    /**
     * The Release instance
     *
     * @access protected
     * @var    Release
     **/
    protected $release;

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
