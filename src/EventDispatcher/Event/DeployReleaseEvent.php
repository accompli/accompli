<?php

namespace Accompli\EventDispatcher\Event;

use Accompli\Deployment\Release;

/**
 * DeployReleaseEvent.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class DeployReleaseEvent extends ReleaseEvent
{
    /**
     * The currently deployed Release instance.
     *
     * @var Release
     */
    private $currentRelease;

    /**
     * Constructs a new DeployReleaseEvent instance.
     *
     * @param Release      $release
     * @param Release|null $currentRelease
     */
    public function __construct(Release $release, Release $currentRelease = null)
    {
        parent::__construct($release);

        $this->currentRelease = $currentRelease;
    }

    /**
     * Returns the currently deployed Release instance.
     *
     * @return Release
     */
    public function getCurrentRelease()
    {
        return $this->currentRelease;
    }
}
