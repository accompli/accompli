<?php

namespace Accompli\Test\Task;

use Accompli\AccompliEvents;
use Accompli\Deployment\Host;
use Accompli\EventDispatcher\Event\InstallReleaseEvent;
use Accompli\Task\YamlConfigurationTask;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * YamlConfigurationTaskTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class YamlConfigurationTaskTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if YamlConfigurationTask::getSubscribedEvents returns an array with at least a AccompliEvents::INSTALL_RELEASE key.
     */
    public function testGetSubscribedEvents()
    {
        $this->assertInternalType('array', YamlConfigurationTask::getSubscribedEvents());
        $this->assertArrayHasKey(AccompliEvents::INSTALL_RELEASE, YamlConfigurationTask::getSubscribedEvents());
    }

    /**
     * Tests if constructing a new YamlConfigurationTask sets the instance properties.
     */
    public function testConstruct()
    {
        $task = new YamlConfigurationTask('/parameters.yml', array('foo' => 'bar', 'baz' => '', 'bar' => array('baz' => '')), array('test' => array('foo' => 'bar')), array('baz', 'bar.baz'));

        $this->assertAttributeSame('/parameters.yml', 'configurationFile', $task);
        $this->assertAttributeSame(array('foo' => 'bar', 'baz' => '', 'bar' => array('baz' => '')), 'configuration', $task);
        $this->assertAttributeSame(array('test' => array('foo' => 'bar')), 'stageSpecificConfigurations', $task);
        $this->assertAttributeSame(array('baz', 'bar.baz'), 'generateValueForParameters', $task);
    }

    /**
     * Tests if YamlConfigurationTask::onInstallReleaseCreateOrUpdateConfiguration creates a new configuration file.
     */
    public function testOnInstallReleaseCreateOrUpdateConfigurationCreatesANewConfigurationFile()
    {
        $eventDispatcherMock = $this->getMockBuilder('Accompli\EventDispatcher\EventDispatcherInterface')
                ->getMock();
        $eventDispatcherMock->expects($this->exactly(2))
                ->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder('Accompli\Deployment\Connection\ConnectionAdapterInterface')
                ->getMock();
        $connectionAdapterMock->expects($this->exactly(3))
                ->method('isFile')
                ->withConsecutive(
                    array($this->equalTo('{workspace}/releases/0.1.0/parameters.yml')),
                    array($this->equalTo('{workspace}/releases/0.1.0/parameters.yml')),
                    array($this->equalTo('{workspace}/releases/0.1.0/parameters.yml.dist'))
                )
                ->willReturnOnConsecutiveCalls(false, false, false);
        $connectionAdapterMock->expects($this->once())
                ->method('putContents')
                ->with($this->equalTo('{workspace}/releases/0.1.0/parameters.yml'), $this->stringStartsWith("foo: bar\nbaz: "))
                ->willReturn(true);

        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())
                ->method('hasConnection')
                ->willReturn(true);
        $hostMock->expects($this->once())
                ->method('getConnection')
                ->willReturn($connectionAdapterMock);

        $workspaceMock = $this->getMockBuilder('Accompli\Deployment\Workspace')
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->exactly(3))
                ->method('getHost')
                ->willReturn($hostMock);

        $releaseMock = $this->getMockBuilder('Accompli\Deployment\Release')
                ->disableOriginalConstructor()
                ->getMock();
        $releaseMock->expects($this->exactly(3))
                ->method('getWorkspace')
                ->willReturn($workspaceMock);
        $releaseMock->expects($this->once())
                ->method('getPath')
                ->willReturn('{workspace}/releases/0.1.0');

        $event = new InstallReleaseEvent($releaseMock);

        $task = new YamlConfigurationTask('/parameters.yml', array('foo' => 'bar', 'baz' => '', 'bar' => array('baz' => '')), array(), array('baz', 'bar.baz'));
        $task->onInstallReleaseCreateOrUpdateConfiguration($event, AccompliEvents::INSTALL_RELEASE, $eventDispatcherMock);
    }

    /**
     * Tests if YamlConfigurationTask::onInstallReleaseCreateOrUpdateConfiguration creates a new configuration file with environment variables.
     */
    public function testOnInstallReleaseCreateOrUpdateConfigurationCreatesANewConfigurationFileWithEnvironmentVariables()
    {
        $eventDispatcherMock = $this->getMockBuilder('Accompli\EventDispatcher\EventDispatcherInterface')
                ->getMock();
        $eventDispatcherMock->expects($this->exactly(2))
                ->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder('Accompli\Deployment\Connection\ConnectionAdapterInterface')
                ->getMock();
        $connectionAdapterMock->expects($this->exactly(3))
                ->method('isFile')
                ->withConsecutive(
                    array($this->equalTo('{workspace}/releases/0.1.0/parameters.yml')),
                    array($this->equalTo('{workspace}/releases/0.1.0/parameters.yml')),
                    array($this->equalTo('{workspace}/releases/0.1.0/parameters.yml.dist'))
                )
                ->willReturnOnConsecutiveCalls(false, false, false);
        $connectionAdapterMock->expects($this->once())
                ->method('putContents')
                ->with($this->equalTo('{workspace}/releases/0.1.0/parameters.yml'), $this->equalTo("foo: bar_test\nbaz: 0.1.0\nbar:\n    baz: test_0.1.0\n"))
                ->willReturn(true);

        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())
                ->method('hasConnection')
                ->willReturn(true);
        $hostMock->expects($this->once())
                ->method('getConnection')
                ->willReturn($connectionAdapterMock);
        $hostMock->expects($this->exactly(2))
                ->method('getStage')
                ->willReturn(Host::STAGE_TEST);

        $workspaceMock = $this->getMockBuilder('Accompli\Deployment\Workspace')
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->exactly(3))
                ->method('getHost')
                ->willReturn($hostMock);

        $releaseMock = $this->getMockBuilder('Accompli\Deployment\Release')
                ->disableOriginalConstructor()
                ->getMock();
        $releaseMock->expects($this->exactly(3))
                ->method('getWorkspace')
                ->willReturn($workspaceMock);
        $releaseMock->expects($this->once())
                ->method('getPath')
                ->willReturn('{workspace}/releases/0.1.0');
        $releaseMock->expects($this->once())
                ->method('getVersion')
                ->willReturn('0.1.0');

        $event = new InstallReleaseEvent($releaseMock);

        $task = new YamlConfigurationTask('/parameters.yml', array('foo' => 'bar_%stage%', 'baz' => '%version%', 'bar' => array('baz' => '%stage%_%version%')), array(), array());
        $task->onInstallReleaseCreateOrUpdateConfiguration($event, AccompliEvents::INSTALL_RELEASE, $eventDispatcherMock);
    }

    /**
     * Tests if YamlConfigurationTask::onInstallReleaseCreateOrUpdateConfiguration updates the existing new configuration file.
     */
    public function testOnInstallReleaseCreateOrUpdateConfigurationUpdatesExistingConfigurationFile()
    {
        $eventDispatcherMock = $this->getMockBuilder('Accompli\EventDispatcher\EventDispatcherInterface')
                ->getMock();
        $eventDispatcherMock->expects($this->exactly(2))
                ->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder('Accompli\Deployment\Connection\ConnectionAdapterInterface')
                ->getMock();
        $connectionAdapterMock->expects($this->exactly(3))
                ->method('isFile')
                ->withConsecutive(
                    array($this->equalTo('{workspace}/releases/0.1.0/parameters.yml')),
                    array($this->equalTo('{workspace}/releases/0.1.0/parameters.yml')),
                    array($this->equalTo('{workspace}/releases/0.1.0/parameters.yml.dist'))
                )
                ->willReturnOnConsecutiveCalls(true, true, false);
        $connectionAdapterMock->expects($this->once())
                ->method('getContents')
                ->with($this->equalTo('{workspace}/releases/0.1.0/parameters.yml'))
                ->willReturn("foo: bam\nbaz: ~\nbar:\n    baz: ~");
        $connectionAdapterMock->expects($this->once())
                ->method('putContents')
                ->with(
                    $this->equalTo('{workspace}/releases/0.1.0/parameters.yml'),
                    $this->logicalNot($this->equalTo("foo: bam\nbaz: ~\nbar:\n    baz: ~"))
                )
                ->willReturn(true);

        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())
                ->method('hasConnection')
                ->willReturn(true);
        $hostMock->expects($this->once())
                ->method('getConnection')
                ->willReturn($connectionAdapterMock);

        $workspaceMock = $this->getMockBuilder('Accompli\Deployment\Workspace')
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->exactly(3))
                ->method('getHost')
                ->willReturn($hostMock);

        $releaseMock = $this->getMockBuilder('Accompli\Deployment\Release')
                ->disableOriginalConstructor()
                ->getMock();
        $releaseMock->expects($this->exactly(3))
                ->method('getWorkspace')
                ->willReturn($workspaceMock);
        $releaseMock->expects($this->once())
                ->method('getPath')
                ->willReturn('{workspace}/releases/0.1.0');

        $event = new InstallReleaseEvent($releaseMock);

        $task = new YamlConfigurationTask('/parameters.yml', array('foo' => 'bar', 'baz' => '', 'bar' => array('baz' => '')), array(), array('baz', 'bar.baz'));
        $task->onInstallReleaseCreateOrUpdateConfiguration($event, AccompliEvents::INSTALL_RELEASE, $eventDispatcherMock);
    }

    /**
     * Tests if YamlConfigurationTask::onInstallReleaseCreateOrUpdateConfiguration creates a new configuration file from the distribution configuration file.
     */
    public function testOnInstallReleaseCreateOrUpdateConfigurationCreatesANewConfigurationFileFromDistributionFile()
    {
        $eventDispatcherMock = $this->getMockBuilder('Accompli\EventDispatcher\EventDispatcherInterface')
                ->getMock();
        $eventDispatcherMock->expects($this->exactly(2))
                ->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder('Accompli\Deployment\Connection\ConnectionAdapterInterface')
                ->getMock();
        $connectionAdapterMock->expects($this->exactly(3))
                ->method('isFile')
                ->withConsecutive(
                    array($this->equalTo('{workspace}/releases/0.1.0/parameters.yml')),
                    array($this->equalTo('{workspace}/releases/0.1.0/parameters.yml')),
                    array($this->equalTo('{workspace}/releases/0.1.0/parameters.yml.dist'))
                )
                ->willReturnOnConsecutiveCalls(false, false, true);
        $connectionAdapterMock->expects($this->once())
                ->method('getContents')
                ->with($this->equalTo('{workspace}/releases/0.1.0/parameters.yml.dist'))
                ->willReturn("foo: bam\nbaz: ~\nbar:\n    baz: ~");
        $connectionAdapterMock->expects($this->once())
                ->method('putContents')
                ->with(
                    $this->equalTo('{workspace}/releases/0.1.0/parameters.yml'),
                    $this->logicalAnd($this->logicalNot($this->equalTo("foo: bam\nbaz: ~\nbar:\n    baz: ~")), $this->stringStartsWith("foo: bar\nbaz: "))
                )
                ->willReturn(true);

        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())
                ->method('hasConnection')
                ->willReturn(true);
        $hostMock->expects($this->once())
                ->method('getConnection')
                ->willReturn($connectionAdapterMock);

        $workspaceMock = $this->getMockBuilder('Accompli\Deployment\Workspace')
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->exactly(3))
                ->method('getHost')
                ->willReturn($hostMock);

        $releaseMock = $this->getMockBuilder('Accompli\Deployment\Release')
                ->disableOriginalConstructor()
                ->getMock();
        $releaseMock->expects($this->exactly(3))
                ->method('getWorkspace')
                ->willReturn($workspaceMock);
        $releaseMock->expects($this->once())
                ->method('getPath')
                ->willReturn('{workspace}/releases/0.1.0');

        $event = new InstallReleaseEvent($releaseMock);

        $task = new YamlConfigurationTask('/parameters.yml', array('foo' => 'bar', 'baz' => '', 'bar' => array('baz' => '')), array(), array('baz', 'bar.baz'));
        $task->onInstallReleaseCreateOrUpdateConfiguration($event, AccompliEvents::INSTALL_RELEASE, $eventDispatcherMock);
    }

    /**
     * Tests if YamlConfigurationTask::onInstallReleaseCreateOrUpdateConfiguration creates a new configuration file with stage specific configuration.
     *
     * @dataProvider provideExpectedStageSpecificConfigurations
     *
     * @param string $stage
     * @param string $expectedConfiguration
     */
    public function testOnInstallReleaseCreateOrUpdateConfigurationCreatesANewStageSpecificConfigurationFile($stage, $expectedConfiguration)
    {
        $eventDispatcherMock = $this->getMockBuilder('Accompli\EventDispatcher\EventDispatcherInterface')
                ->getMock();

        $connectionAdapterMock = $this->getMockBuilder('Accompli\Deployment\Connection\ConnectionAdapterInterface')
                ->getMock();
        $connectionAdapterMock->expects($this->exactly(3))
                ->method('isFile')
                ->withConsecutive(
                    array($this->equalTo('{workspace}/releases/0.1.0/parameters.yml')),
                    array($this->equalTo('{workspace}/releases/0.1.0/parameters.yml')),
                    array($this->equalTo('{workspace}/releases/0.1.0/parameters.yml.dist'))
                )
                ->willReturnOnConsecutiveCalls(false, false, true);
        $connectionAdapterMock->expects($this->once())
                ->method('getContents')
                ->with($this->equalTo('{workspace}/releases/0.1.0/parameters.yml.dist'))
                ->willReturn("foo: bam\nbaz: ~\nbar:\n    baz: ~");
        $connectionAdapterMock->expects($this->once())
                ->method('putContents')
                ->with(
                    $this->equalTo('{workspace}/releases/0.1.0/parameters.yml'),
                    $this->equalTo($expectedConfiguration)
                )
                ->willReturn(true);

        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->exactly(2))
                ->method('getStage')
                ->willReturn($stage);
        $hostMock->expects($this->once())
                ->method('hasConnection')
                ->willReturn(true);
        $hostMock->expects($this->once())
                ->method('getConnection')
                ->willReturn($connectionAdapterMock);

        $workspaceMock = $this->getMockBuilder('Accompli\Deployment\Workspace')
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->exactly(3))
                ->method('getHost')
                ->willReturn($hostMock);

        $releaseMock = $this->getMockBuilder('Accompli\Deployment\Release')
                ->disableOriginalConstructor()
                ->getMock();
        $releaseMock->expects($this->exactly(3))
                ->method('getWorkspace')
                ->willReturn($workspaceMock);
        $releaseMock->expects($this->once())
                ->method('getPath')
                ->willReturn('{workspace}/releases/0.1.0');

        $event = new InstallReleaseEvent($releaseMock);

        $stageSpecificConfigurations = array(
            'production' => array(
                'foo' => 'bam',
            ),
            'acceptance' => array(
                'foo' => 'baz',
            ),
            'test' => array(
                'foo' => 'foo',
            ),
        );

        $task = new YamlConfigurationTask('/parameters.yml', array('foo' => 'bar', 'baz' => '', 'bar' => array('baz' => '')), $stageSpecificConfigurations);
        $task->onInstallReleaseCreateOrUpdateConfiguration($event, AccompliEvents::INSTALL_RELEASE, $eventDispatcherMock);
    }

    /**
     * Tests if YamlConfigurationTask::onInstallReleaseCreateOrUpdateConfiguration fails creating a new configuration file.
     */
    public function testOnInstallReleaseCreateOrUpdateConfigurationFailsCreatingANewConfigurationFile()
    {
        $eventDispatcherMock = $this->getMockBuilder('Accompli\EventDispatcher\EventDispatcherInterface')
                ->getMock();
        $eventDispatcherMock->expects($this->exactly(2))
                ->method('dispatch');

        $connectionAdapterMock = $this->getMockBuilder('Accompli\Deployment\Connection\ConnectionAdapterInterface')
                ->getMock();
        $connectionAdapterMock->expects($this->exactly(3))
                ->method('isFile')
                ->withConsecutive(
                    array($this->equalTo('{workspace}/releases/0.1.0/parameters.yml')),
                    array($this->equalTo('{workspace}/releases/0.1.0/parameters.yml')),
                    array($this->equalTo('{workspace}/releases/0.1.0/parameters.yml.dist'))
                )
                ->willReturnOnConsecutiveCalls(false, false, false);
        $connectionAdapterMock->expects($this->once())
                ->method('putContents')
                ->with($this->equalTo('{workspace}/releases/0.1.0/parameters.yml'), $this->stringStartsWith("foo: bar\nbaz: "))
                ->willReturn(false);

        $hostMock = $this->getMockBuilder('Accompli\Deployment\Host')
                ->disableOriginalConstructor()
                ->getMock();
        $hostMock->expects($this->once())
                ->method('hasConnection')
                ->willReturn(true);
        $hostMock->expects($this->once())
                ->method('getConnection')
                ->willReturn($connectionAdapterMock);

        $workspaceMock = $this->getMockBuilder('Accompli\Deployment\Workspace')
                ->disableOriginalConstructor()
                ->getMock();
        $workspaceMock->expects($this->exactly(3))
                ->method('getHost')
                ->willReturn($hostMock);

        $releaseMock = $this->getMockBuilder('Accompli\Deployment\Release')
                ->disableOriginalConstructor()
                ->getMock();
        $releaseMock->expects($this->exactly(3))
                ->method('getWorkspace')
                ->willReturn($workspaceMock);
        $releaseMock->expects($this->once())
                ->method('getPath')
                ->willReturn('{workspace}/releases/0.1.0');

        $event = new InstallReleaseEvent($releaseMock);

        $task = new YamlConfigurationTask('/parameters.yml', array('foo' => 'bar', 'baz' => '', 'bar' => array('baz' => '')), array(), array('baz', 'bar.baz'));
        $task->onInstallReleaseCreateOrUpdateConfiguration($event, AccompliEvents::INSTALL_RELEASE, $eventDispatcherMock);
    }

    /**
     * Returns an array with the stage and the expected stage specific configuration.
     *
     * @return array
     */
    public function provideExpectedStageSpecificConfigurations()
    {
        return array(
            array('test', "foo: foo\nbaz: ''\nbar:\n    baz: ''\n"),
            array('acceptance', "foo: baz\nbaz: ''\nbar:\n    baz: ''\n"),
            array('production', "foo: bam\nbaz: ''\nbar:\n    baz: ''\n"),
        );
    }

    /**
     * Tests if generated values are not regenerated within the same process.
     */
    public function testGeneratedValuesAreNotRegenerated()
    {
        $task = new YamlConfigurationTask('foobar');
        $reflectionClass = new ReflectionClass('Accompli\Task\YamlConfigurationTask');
        $method = $reflectionClass->getMethod('generateValue');
        $method->setAccessible(true);

        $generated = $method->invoke($task, 'foobar');

        $this->assertNotEmpty($generated);
        $this->assertEquals($generated, $method->invoke($task, 'foobar'));
    }
}
