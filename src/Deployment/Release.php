<?php

namespace Accompli\Deployment;

/**
 * Release.
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
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
     * The unique identifier identifing this Release.
     *
     * @var string
     */
    private $identifier;

    /**
     * Constructs a new Release instance
     *
     * @param string $identifier
     */
    public function __construct($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * Returns the Workspace instance
     *
     * @return Workspace
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    /**
     * Returns the release identifier
     *
     * @return string
     */
    public function getIdenfifier()
    {
        return $this->identifier;
    }

    /**
     * Returns the path of this Release
     *
     * @return string
     */
    public function getPath()
    {
        return sprintf('%s/%s', $this->workspace->getHost()->getPath(), $this->getIdenfifier());
    }

    /**
     * Sets the Workspace instance
     *
     * @param Workspace $workspace
     */
    public function setWorkspace(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }
}
