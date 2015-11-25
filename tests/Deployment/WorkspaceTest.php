<?php

namespace Accompli\Test;

use Accompli\Deployment\Host;
use Accompli\Deployment\Release;
use Accompli\Deployment\Workspace;
use PHPUnit_Framework_TestCase;

/**
 * WorkspaceTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class WorkspaceTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if constructing a new Workspace instance sets the instance properties.
     */
    public function testConstruct()
    {
        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();

        $workspace = new Workspace($hostMock);

        $this->assertAttributeSame($hostMock, 'host', $workspace);
    }

    /**
     * Tests if Workspace::getHost returns the same instance as set as constructor argument.
     */
    public function testGetHost()
    {
        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();

        $workspace = new Workspace($hostMock);

        $this->assertSame($hostMock, $workspace->getHost());
    }

    /**
     * Tests if Workspace::setReleasesDirectory sets the property.
     */
    public function testSetReleasesDirectory()
    {
        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();

        $workspace = new Workspace($hostMock);
        $workspace->setReleasesDirectory('releases/');

        $this->assertAttributeSame('releases/', 'releasesDirectory', $workspace);
    }

    /**
     * Tests if Workspace::setDataDirectory sets the property.
     */
    public function testSetDataDirectory()
    {
        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();

        $workspace = new Workspace($hostMock);
        $workspace->setDataDirectory('data/');

        $this->assertAttributeSame('data/', 'dataDirectory', $workspace);
    }

    /**
     * Tests if Workspace::setCacheDirectory sets the property.
     */
    public function testSetCacheDirectory()
    {
        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();

        $workspace = new Workspace($hostMock);
        $workspace->setCacheDirectory('cache/');

        $this->assertAttributeSame('cache/', 'cacheDirectory', $workspace);
    }

    /**
     * Tests if Workspace::testSetOtherDirectories sets the property.
     */
    public function testSetOtherDirectories()
    {
        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();

        $workspace = new Workspace($hostMock);
        $workspace->setOtherDirectories(array('data/images/', 'data/documents/'));

        $this->assertAttributeSame(array('data/images/', 'data/documents/'), 'otherDirectories', $workspace);
    }

    /**
     * Tests if Workspace::getReleasesDirectory returns the absolute path to the releases directory.
     *
     * @depends testSetReleasesDirectory
     */
    public function testGetReleasesDirectory()
    {
        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())->method('getPath')->willReturn('{host-base-path}');

        $workspace = new Workspace($hostMock);
        $workspace->setReleasesDirectory('releases/');

        $this->assertSame('{host-base-path}/releases/', $workspace->getReleasesDirectory());
    }

    /**
     * Tests if Workspace::getDataDirectory returns the absolute path to the data directory.
     *
     * @depends testSetDataDirectory
     */
    public function testGetDataDirectory()
    {
        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())->method('getPath')->willReturn('{host-base-path}');

        $workspace = new Workspace($hostMock);
        $workspace->setDataDirectory('data/');

        $this->assertSame('{host-base-path}/data/', $workspace->getDataDirectory());
    }

    /**
     * Tests if Workspace::getCacheDirectory returns the absolute path to the cache directory.
     *
     * @depends testSetCacheDirectory
     */
    public function testGetCacheDirectory()
    {
        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())->method('getPath')->willReturn('{host-base-path}');

        $workspace = new Workspace($hostMock);
        $workspace->setCacheDirectory('cache/');

        $this->assertSame('{host-base-path}/cache/', $workspace->getCacheDirectory());
    }

    /**
     * Tests if Workspace::getOtherDirectories returns an array with the absolute path to the other directories.
     *
     * @depends testSetOtherDirectories
     */
    public function testGetOtherDirectories()
    {
        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->exactly(2))->method('getPath')->willReturn('{host-base-path}');

        $workspace = new Workspace($hostMock);
        $workspace->setOtherDirectories(array('data/images/', 'data/documents/'));

        $this->assertSame(array('{host-base-path}/data/images/', '{host-base-path}/data/documents/'), $workspace->getOtherDirectories());
    }

    /**
     * Tests if Workspace::addRelease adds the Release to the Workspace and sets the Workspace on the Release.
     */
    public function testAddReleaseSetsWorkspaceOnRelease()
    {
        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();

        $release = new Release('1.0.0');

        $workspace = new Workspace($hostMock);
        $workspace->addRelease($release);

        $this->assertAttributeSame(array($release), 'releases', $workspace);
        $this->assertSame($workspace, $release->getWorkspace());
    }

    /**
     * Tests if Workspace::getReleases returns an empty array by default.
     */
    public function testGetReleasesReturnsEmptyArray()
    {
        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();

        $workspace = new Workspace($hostMock);

        $this->assertInternalType('array', $workspace->getReleases());
        $this->assertEmpty($workspace->getReleases());
    }

    /**
     * Tests if Workspace::getReleases returns the array with added Release instances.
     *
     * @depends testAddReleaseSetsWorkspaceOnRelease
     */
    public function testGetReleasesReturnsArrayWithReleaseInstanceAfterAddRelease()
    {
        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();

        $release = new Release('1.0.0');

        $workspace = new Workspace($hostMock);
        $workspace->addRelease($release);

        $this->assertSame(array($release), $workspace->getReleases());
    }
}
