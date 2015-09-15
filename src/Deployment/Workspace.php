<?php

namespace Accompli\Deployment;

/**
 * Workspace.
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 */
class Workspace
{
    /**
     * The Host instance.
     *
     * @var Host
     */
    private $host;

    /**
     * The array with Release instances.
     *
     * @var Release[]
     */
    private $releases = array();

    /**
     * The array with user data directories.
     *
     * @var array
     */
    private $userDataDirectories = array();

    /**
     * Constructs a new Workspace instance
     *
     * @param Host $host
     */
    public function __construct(Host $host)
    {
        $this->host = $host;
    }

    /**
     * Returns the Host instance
     *
     * @return Host
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Adds a Release instance to this Workspace
     *
     * @param Release $release
     */
    public function addRelease(Release $release)
    {
        $release->setWorkspace($this);

        $this->releases[] = $release;
    }

    /**
     * Returns the array with Release instances
     *
     * @return Release[]
     */
    public function getReleases()
    {
        return $this->releases;
    }

    /**
     * Adds a user data directory
     *
     * @param string $identifier
     * @param string $path
     */
    public function addUserDataDirectory($identifier, $path)
    {
        $this->userDataDirectories[$identifier] = $path;
    }

    /**
     * Returns a user data directory by identifier
     *
     * @param string $identifier
     *
     * @return string|null
     */
    public function getUserDataDirectory($identifier)
    {
        if (isset($this->userDataDirectories[$identifier])) {
            return $this->userDataDirectories[$identifier];
        }
    }

    /**
     * Unsets a user data directory by identifier
     *
     * @param string $identifier
     */
    public function unsetUserDataDirectory($identifier)
    {
        unset($this->userDataDirectories[$identifier]);
    }
}
