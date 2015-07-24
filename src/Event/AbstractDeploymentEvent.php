<?php

namespace Accompli\Event;

use Accompli\Release;
use Symfony\Component\EventDispatcher\Event;

/**
 * AbstractDeploymentEvent
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 * @package Accompli\Event
 **/
abstract class AbstractDeploymentEvent extends Event
{
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
     * Constructs a new AbstractDeploymentEvent instance
     *
     * @access public
     * @param  Release $release
     * @return void
     **/
    public function __construct(Release $release)
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
