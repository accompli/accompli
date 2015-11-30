<?php

namespace Accompli\EventDispatcher\Event;

use Accompli\Deployment\Release;

/**
 * PrepareDeployReleaseEvent.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class PrepareDeployReleaseEvent extends PrepareReleaseEvent
{
    /**
     * The currently deployed Release instance.
     *
     * @var Release
     */
    private $currentRelease;

    /**
     * Returns the currently deployed Release instance.
     *
     * @return Release
     */
    public function getCurrentRelease()
    {
        return $this->currentRelease;
    }

    /**
     * Sets the currently deployed Release instance.
     *
     * @param Release $release
     */
    public function setCurrentRelease(Release $release)
    {
        $this->currentRelease = $release;
    }
}
