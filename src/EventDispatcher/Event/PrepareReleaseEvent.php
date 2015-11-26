<?php

namespace Accompli\EventDispatcher\Event;

use Accompli\Deployment\Release;
use Accompli\Deployment\Workspace;
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
     * The version string.
     *
     * @var string
     */
    private $version;

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
     * @param string    $version
     */
    public function __construct(Workspace $workspace, $version)
    {
        $this->workspace = $workspace;
        $this->version = $version;
    }

    /**
     * Returns the Workspace instance.
     *
     * @return Workspace
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    /**
     * Returns the version.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
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

    /**
     * Sets a Release instance.
     *
     * @param Release $release
     */
    public function setRelease(Release $release)
    {
        $this->release = $release;
    }
}
