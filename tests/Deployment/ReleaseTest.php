<?php

namespace Accompli\Test\Deployment;

use Accompli\Deployment\Release;
use PHPUnit_Framework_TestCase;

/**
 * ReleaseTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class ReleaseTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if constructing a new Release instance sets the instance properties.
     */
    public function testConstruct()
    {
        $release = new Release('0.1.0');

        $this->assertAttributeSame('0.1.0', 'version', $release);
    }

    /**
     * Tests if Release::setWorkspace sets the workspace property.
     */
    public function testSetWorkspace()
    {
        $workspaceMock = $this->getMockBuilder('Accompli\Deployment\Workspace')
                ->disableOriginalConstructor()
                ->getMock();

        $release = new Release('0.1.0');
        $release->setWorkspace($workspaceMock);

        $this->assertAttributeSame($workspaceMock, 'workspace', $release);
    }

    /**
     * Tests if Release::getWorkspace returns the Workspace instance set by Release::setWorkspace.
     *
     * @depends testSetWorkspace
     */
    public function testGetWorkspace()
    {
        $workspaceMock = $this->getMockBuilder('Accompli\Deployment\Workspace')
                ->disableOriginalConstructor()
                ->getMock();

        $release = new Release('0.1.0');
        $release->setWorkspace($workspaceMock);

        $this->assertSame($workspaceMock, $release->getWorkspace());
    }

    /**
     * Tests if Release::getVersion returns the version set as constructor argument.
     *
     * @depends testConstruct
     */
    public function testGetVersion()
    {
        $release = new Release('0.1.0');

        $this->assertSame('0.1.0', $release->getVersion());
    }

    /**
     * Tests if Release::getPath returns the absolute path to the directory of the release.
     *
     * @depends testSetWorkspace
     */
    public function testGetPath()
    {
        $workspaceMock = $this->getMockBuilder('Accompli\Deployment\Workspace')
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->once())->method('getReleasesDirectory')->willReturn('{releases-directory}');

        $release = new Release('0.1.0');
        $release->setWorkspace($workspaceMock);

        $this->assertSame('{releases-directory}/0.1.0', $release->getPath());
    }
}
