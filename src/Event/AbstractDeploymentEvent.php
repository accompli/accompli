<?php

namespace Accompli\Event;

use Accompli\Release;
use Symfony\Component\EventDispatcher\Event;

/**
 * AbstractDeploymentEvent.
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 */
abstract class AbstractDeploymentEvent extends Event
{
    /**
     * The Release instance.
     *
     * @var Release
     */
    protected $release;

    /**
     * Constructs a new AbstractDeploymentEvent instance
     *
     * @param Release $release
     */
    public function __construct(Release $release)
    {
        $this->release = $release;
    }

    /**
     * Returns the Release instance
     *
     * @return Release
     */
    public function getRelease()
    {
        return $this->release;
    }
}
