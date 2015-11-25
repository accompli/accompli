<?php

namespace Accompli\Deployment;

/**
 * Release.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class Release
{
    /**
     * The Workspace instance.
     *
     * @var Workspace
     */
    private $workspace;

    /**
     * The version identifying this Release.
     *
     * @var string
     */
    private $version;

    /**
     * Constructs a new Release instance.
     *
     * @param string $version
     */
    public function __construct($version)
    {
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
     * Returns the version identifying this Release.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Returns the path of this Release.
     *
     * @return string
     */
    public function getPath()
    {
        return sprintf('%s/%s', $this->workspace->getReleasesDirectory(), $this->getVersion());
    }

    /**
     * Sets the Workspace instance.
     *
     * @param Workspace $workspace
     */
    public function setWorkspace(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }
}
