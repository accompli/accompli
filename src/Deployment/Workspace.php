<?php

namespace Accompli\Deployment;

/**
 * Workspace.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
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
     * The relative path to the directory containing the releases.
     *
     * @var string
     */
    private $releasesDirectory;

    /**
     * The relative path to the directory containing user data.
     *
     * @var string
     */
    private $dataDirectory;

    /**
     * The relative path to the directory containing cache data.
     *
     * @var string
     */
    private $cacheDirectory;

    /**
     * The array with other directories within the workspace.
     *
     * @var array
     */
    private $otherDirectories = array();

    /**
     * The array with Release instances.
     *
     * @var Release[]
     */
    private $releases = array();

    /**
     * Constructs a new Workspace instance.
     *
     * @param Host $host
     */
    public function __construct(Host $host)
    {
        $this->host = $host;
    }

    /**
     * Returns the Host instance.
     *
     * @return Host
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Returns the absolute path to the directory containing the releases.
     *
     * @return string
     */
    public function getReleasesDirectory()
    {
        return sprintf('%s/%s', $this->getHost()->getPath(), $this->releasesDirectory);
    }

    /**
     * Returns the absolute path to the directory containing the user data.
     *
     * @return string
     */
    public function getDataDirectory()
    {
        return sprintf('%s/%s', $this->getHost()->getPath(), $this->dataDirectory);
    }

    /**
     * Returns the absolute path to the directory containing the cache data.
     *
     * @return string
     */
    public function getCacheDirectory()
    {
        return sprintf('%s/%s', $this->getHost()->getPath(), $this->cacheDirectory);
    }

    /**
     * Returns the array with absolute paths to other directories.
     *
     * @return array
     */
    public function getOtherDirectories()
    {
        $directories = array();
        foreach ($this->otherDirectories as $directory) {
            $directories[] = sprintf('%s/%s', $this->getHost()->getPath(), $directory);
        }

        return $directories;
    }

    /**
     * Returns the array with Release instances.
     *
     * @return Release[]
     */
    public function getReleases()
    {
        return $this->releases;
    }

    /**
     * Sets the relative path to the directory containing the releases.
     *
     * @param string $releasesDirectory
     */
    public function setReleasesDirectory($releasesDirectory)
    {
        $this->releasesDirectory = $releasesDirectory;
    }

    /**
     * Sets the relative path to the directory containing user data.
     *
     * @param string $dataDirectory
     */
    public function setDataDirectory($dataDirectory)
    {
        $this->dataDirectory = $dataDirectory;
    }

    /**
     * Sets the relative path to the directory containing the cache.
     *
     * @param string $cacheDirectory
     */
    public function setCacheDirectory($cacheDirectory)
    {
        $this->cacheDirectory = $cacheDirectory;
    }

    /**
     * Sets the array with relative paths for other directories.
     *
     * @param array $directories
     */
    public function setOtherDirectories(array $directories)
    {
        $this->otherDirectories = $directories;
    }

    /**
     * Adds a Release instance to this Workspace.
     *
     * @param Release $release
     */
    public function addRelease(Release $release)
    {
        $release->setWorkspace($this);

        $this->releases[] = $release;
    }
}
