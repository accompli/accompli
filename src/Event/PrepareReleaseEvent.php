<?php

namespace Accompli\Event;

use Accompli\Deployment\Workspace;
use Accompli\Release;
use Symfony\Component\EventDispatcher\Event;

/**
 * PrepareReleaseEvent.
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 */
class PrepareReleaseEvent extends Event
{
    /**
     * The Workspace instance.
     *
     * @var Workspace
     */
    private $workspace;

    /**
     * The Release instance.
     *
     * @var Release
     */
    protected $release;

    /**
     * Constructs a new PrepareReleaseEvent.
     *
     * @param Workspace $workspace
     */
    public function __construct(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    /**
     * Sets a Release instance.
     *
     * @param Release $release
     */
    public function setRelease(Release $release)
    {
        $this->release = $release;
    }

    /**
     * Returns the Release instance.
     *
     * @return Release
     */
    public function getRelease()
    {
        return $this->release;
    }
}
