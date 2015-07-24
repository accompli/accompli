<?php

namespace Accompli\Deployment;

/**
 * Release
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 * @package Accompli\Deployment
 **/
class Release
{
    /**
     * The Workspace instance
     *
     * @access private
     * @var Workspace
     **/
    private $workspace;

    /**
     * The unique identifier identifing this Release
     *
     * @access private
     * @var string
     **/
    private $identifier;

    /**
     * __construct
     *
     * Constructs a new Release instance
     *
     * @access public
     * @param  string $identifier
     * @return null
     **/
    public function __construct($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * getWorkspace
     *
     * Returns the Workspace instance
     *
     * @access public
     * @return Workspace
     **/
    public function getWorkspace()
    {
        return $this->workspace;
    }

    /**
     * getIdenfifier
     *
     * Returns the release identifier
     *
     * @access public
     * @return string
     **/
    public function getIdenfifier()
    {
        return $this->identifier;
    }

    /**
     * getPath
     *
     * Returns the path of this Release
     *
     * @access public
     * @return string
     **/
    public function getPath()
    {
        return sprintf('%s/%s', $this->workspace->getHost()->getPath(), $this->getIdenfifier());
    }

    /**
     * setWorkspace
     *
     * Sets the Workspace instance
     *
     * @access public
     * @param  Workspace $workspace
     * @return null
     **/
    public function setWorkspace(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }
}
