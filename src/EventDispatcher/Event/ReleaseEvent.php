<?php

namespace Accompli\EventDispatcher\Event;

use Accompli\Deployment\Release;
use Symfony\Component\EventDispatcher\Event;

/**
 * ReleaseEvent.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class ReleaseEvent extends Event
{
    /**
     * The Release instance.
     *
     * @var Release
     */
    private $release;

    /**
     * Constructs a new ReleaseEvent.
     *
     * @param Release $release
     */
    public function __construct(Release $release)
    {
        $this->release = $release;
    }

    /**
     * Returns the release instance.
     *
     * @return Release
     */
    public function getRelease()
    {
        return $this->release;
    }
}
