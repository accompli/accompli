<?php

namespace Accompli\Deployment;

/**
 * Workspace
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 * @package Accompli\Deployment
 **/
class Workspace
{
    /**
     * The Host instance
     *
     * @access private
     * @var Host
     **/
    private $host;

    /**
     * The array with Release instances
     *
     * @access private
     * @var Release[]
     **/
    private $releases = array();

    /**
     * The array with user data directories
     *
     * @access private
     * @var array
     **/
    private $userDataDirectories = array();

    /**
     * __construct
     *
     * Constructs a new Workspace instance
     *
     * @access public
     * @param  Host $host
     * @return null
     **/
    public function __construct(Host $host)
    {
        $this->host = $host;
    }

    /**
     * getHost
     *
     * Returns the Host instance
     *
     * @access public
     * @return Host
     **/
    public function getHost()
    {
        return $this->host;
    }

    /**
     * addRelease
     *
     * Adds a Release instance to this Workspace
     *
     * @access public
     * @param  Release $release
     * @return null
     **/
    public function addRelease(Release $release)
    {
        $release->setWorkspace($this);

        $this->releases[] = $release;
    }

    /**
     * getReleases
     *
     * Returns the array with Release instances
     *
     * @access public
     * @return Release[]
     **/
    public function getReleases()
    {
        return $this->releases;
    }

    /**
     * addUserDataDirectory
     *
     * Adds a user data directory
     *
     * @access public
     * @param  string $identifier
     * @param  string $path
     * @return null
     **/
    public function addUserDataDirectory($identifier, $path)
    {
        $this->userDataDirectories[$identifier] = $path;
    }

    /**
     * getUserDataDirectory
     *
     * Returns a user data directory by identifier
     *
     * @access public
     * @param  string      $identifier
     * @return string|null
     **/
    public function getUserDataDirectory($identifier)
    {
        if (isset($this->userDataDirectories[$identifier])) {
            return $this->userDataDirectories[$identifier];
        }
    }

    /**
     * unsetUserDataDirectory
     *
     * Unsets a user data directory by identifier
     *
     * @access public
     * @param  string $identifier
     * @return null
     **/
    public function unsetUserDataDirectory($identifier)
    {
        unset($this->userDataDirectories[$identifier]);
    }
}
