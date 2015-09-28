<?php

namespace Accompli\Test;

use Accompli\Deployment\Host;
use Accompli\Deployment\Release;
use Accompli\Deployment\Workspace;
use PHPUnit_Framework_TestCase;

/**
 * WorkspaceTest.
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 */
class WorkspaceTest extends PHPUnit_Framework_TestCase
{
    /**
     * testGetHost.
     */
    public function testGetHost()
    {
        $host = $this->createHostInstance();
        $workspace = $this->createWorkspaceInstance($host);

        $this->assertSame($host, $workspace->getHost());
    }

    /**
     * testAddReleaseSetsWorkspaceOnRelease.
     */
    public function testAddReleaseSetsWorkspaceOnRelease()
    {
        $release = $this->createReleaseInstance();
        $workspace = $this->createWorkspaceInstance($this->createHostInstance());
        $workspace->addRelease($release);

        $this->assertSame($workspace, $release->getWorkspace());
    }

    /**
     * testGetReleasesReturnsEmptyArray.
     */
    public function testGetReleasesReturnsEmptyArray()
    {
        $workspace = $this->createWorkspaceInstance($this->createHostInstance());

        $this->assertInternalType('array', $workspace->getReleases());
        $this->assertEmpty($workspace->getReleases());
    }

    /**
     * testGetReleasesReturnsArrayWithReleaseInstanceAfterAddRelease.
     */
    public function testGetReleasesReturnsArrayWithReleaseInstanceAfterAddRelease()
    {
        $release = $this->createReleaseInstance();
        $workspace = $this->createWorkspaceInstance($this->createHostInstance());
        $workspace->addRelease($release);

        $releases = $workspace->getReleases();
        $this->assertInternalType('array', $releases);
        $this->assertArrayHasKey(0, $releases);
        $this->assertSame($release, $releases[0]);
    }

    /**
     * Constructs and returns a new Workspace instance.
     *
     * @param Host $host
     *
     * @return Workspace
     */
    private function createWorkspaceInstance(Host $host)
    {
        return new Workspace($host);
    }

    /**
     * Constructs and returns a new Host instance.
     *
     * @param string $stage
     * @param string $connectionType
     * @param string $hostname
     * @param string $path
     *
     * @return Host
     */
    private function createHostInstance($stage = Host::STAGE_TEST, $connectionType = 'local', $hostname = 'localhost', $path = '/var/www')
    {
        return new Host($stage, $connectionType, $hostname, $path);
    }

    /**
     * Constructs and returns a new Release instance.
     *
     * @param string $identifier
     *
     * @return Release
     */
    private function createReleaseInstance($identifier = '1.0.0')
    {
        return new Release($identifier);
    }
}
