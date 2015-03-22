<?php

namespace Accompli\Event;

use Accompli\Accompli;
use Accompli\Release;

/**
 * AbstractDeploymentEvent
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 * @package Accompli\Event
 **/
abstract class AbstractDeploymentEvent extends AbstractEvent
{
    /**
     * The Release instance
     *
     * @access protected
     * @var    Release
     **/
    protected $release;

    /**
     * __construct
     *
     * Constructs a new AbstractReleaseEvent instance
     *
     * @access public
     * @param  Accompli $accompli
     * @param  Release  $release
     * @return void
     **/
    public function __construct(Accompli $accompli, Release $release)
    {
        parent::__construct($accompli);

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
