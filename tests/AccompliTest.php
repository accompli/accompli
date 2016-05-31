<?php

namespace Accompli\Test;

use Accompli\Accompli;
use Accompli\Configuration\ConfigurationInterface;
use Accompli\Deployment\Connection\ConnectionManagerInterface;
use Accompli\Deployment\Host;
use Accompli\Deployment\Strategy\DeploymentStrategyInterface;
use Accompli\EventDispatcher\EventDispatcherInterface;
use Nijens\ProtocolStream\StreamManager;
use PHPUnit_Framework_TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * AccompliTest.
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 */
class AccompliTest extends PHPUnit_Framework_TestCase
{
    /**
     * The array with service container parameters.
     *
     * @var array
     */
    private $serviceContainerParameters = array();

    /**
     * Creates a OutputInterface mock.
     */
    public function setUp()
    {
        $outputInterfaceMock = $this->getMockBuilder(OutputInterface::class)
                ->getMock();

        $this->serviceContainerParameters = array(
            'configuration.file' => __DIR__.'/Resources/accompli-with-mock-listeners.json',
            'console.output_interface' => $outputInterfaceMock,
        );
    }

    /**
     * Unregisters the accompli stream wrapper.
     */
    public function tearDown()
    {
        StreamManager::create()->unregisterStream('accompli');
    }

    /**
     * Tests instantiation of Accompli.
     */
    public function testConstruct()
    {
        new Accompli(new ParameterBag());
    }

    /**
     * Tests if Accompli::initializeStreamWrapper initializes the stream wrapper for recipes.
     */
    public function testInitializeStreamWrapper()
    {
        $accompli = new Accompli(new ParameterBag());
        $accompli->initializeStreamWrapper();

        $this->assertContains('accompli', stream_get_wrappers());
        $this->assertFileExists('accompli://recipe/defaults.json');
    }

    /**
     * Tests if Accompli::getContainer returns a service container after Accompli::initializeContainer.
     */
    public function testGetContainer()
    {
        $accompli = new Accompli(new ParameterBag($this->serviceContainerParameters));
        $accompli->initializeContainer();

        $this->assertInstanceOf(ContainerInterface::class, $accompli->getContainer());
    }

    /**
     * Tests if Accompli::initializeContainer initializes the required services in the service container.
     *
     * @depends testGetContainer
     * @dataProvider provideServiceContainerServices
     *
     * @param string $serviceId
     * @param string $serviceInterface
     */
    public function testInitializeContainer($serviceId, $serviceInterface)
    {
        $accompli = new Accompli(new ParameterBag($this->serviceContainerParameters));
        $accompli->initializeContainer();

        $this->assertTrue($accompli->getContainer()->has($serviceId));
        $this->assertInstanceOf($serviceInterface, $accompli->getContainer()->get($serviceId));
    }

    /**
     * Tests if Accompli::getConfiguration returns an instanceof Accompli\Configuration\ConfigurationInterface.
     *
     * @depends testInitializeContainer
     */
    public function testGetConfiguration()
    {
        $accompli = new Accompli(new ParameterBag($this->serviceContainerParameters));
        $accompli->initializeContainer();

        $this->assertInstanceOf(ConfigurationInterface::class, $accompli->getConfiguration());
    }

    /**
     * Tests if Accompli::initializeEventListeners registers the event listeners configured in the configuration to the event dispatcher service.
     *
     * @depends testGetConfiguration
     */
    public function testInitializeEventListeners()
    {
        $accompli = new Accompli(new ParameterBag($this->serviceContainerParameters));
        $accompli->initialize();

        $eventDispatcher = $accompli->getContainer()->get('event_dispatcher');

        $this->assertInternalType('array', $eventDispatcher->getListeners('listener_event'));
        $this->assertCount(1, $eventDispatcher->getListeners('listener_event'));
        $this->assertInternalType('array', $eventDispatcher->getListeners('subscribed_event'));
        $this->assertCount(1, $eventDispatcher->getListeners('subscribed_event'));
    }

    /**
     * Tests if Accompli::install calls the install method on the deployment strategy registered in the service container.
     */
    public function testInstall()
    {
        $deploymentStrategyMock = $this->getMockBuilder(DeploymentStrategyInterface::class)
                ->getMock();
        $deploymentStrategyMock->expects($this->once())
                ->method('install')
                ->with(
                    $this->equalTo('0.1.0'),
                    $this->equalTo(null)
                )
                ->willReturn(true);

        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();
        $eventDispatcherMock->expects($this->once())
                ->method('dispatch');

        $containerMock = $this->getMockBuilder(ContainerInterface::class)
                ->getMock();
        $containerMock->expects($this->exactly(2))
                ->method('get')
                ->withConsecutive(
                    array($this->equalTo('deployment_strategy')),
                    array($this->equalTo('event_dispatcher'))
                )
                ->willReturnOnConsecutiveCalls(
                    $deploymentStrategyMock,
                    $eventDispatcherMock
                );

        $parameterBagMock = $this->getMockBuilder(ParameterBagInterface::class)
                ->getMock();

        $accompli = $this->getMockBuilder(Accompli::class)
                ->setConstructorArgs(array($parameterBagMock))
                ->setMethods(array('getContainer'))
                ->getMock();
        $accompli->expects($this->exactly(2))
                ->method('getContainer')
                ->willReturn($containerMock);

        $this->assertTrue($accompli->install('0.1.0'));
    }

    /**
     * Tests if Accompli::deploy calls the deploy method on the deployment strategy registered in the service container.
     */
    public function testDeploy()
    {
        $deploymentStrategyMock = $this->getMockBuilder(DeploymentStrategyInterface::class)
                ->getMock();
        $deploymentStrategyMock->expects($this->once())
                ->method('deploy')
                ->with(
                    $this->equalTo('0.1.0'),
                    $this->equalTo(Host::STAGE_TEST)
                )
                ->willReturn(true);

        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)
                ->getMock();
        $eventDispatcherMock->expects($this->once())
                ->method('dispatch');

        $containerMock = $this->getMockBuilder(ContainerInterface::class)
                ->getMock();
        $containerMock->expects($this->exactly(2))
                ->method('get')
                ->withConsecutive(
                    array($this->equalTo('deployment_strategy')),
                    array($this->equalTo('event_dispatcher'))
                )
                ->willReturnOnConsecutiveCalls(
                    $deploymentStrategyMock,
                    $eventDispatcherMock
                );

        $parameterBagMock = $this->getMockBuilder(ParameterBagInterface::class)
                ->getMock();

        $accompli = $this->getMockBuilder(Accompli::class)
                ->setConstructorArgs(array($parameterBagMock))
                ->setMethods(array('getContainer'))
                ->getMock();
        $accompli->expects($this->exactly(2))
                ->method('getContainer')
                ->willReturn($containerMock);

        $this->assertTrue($accompli->deploy('0.1.0', Host::STAGE_TEST));
    }

    /**
     * Returns an array with services that should be defined in the service container.
     *
     * @return array
     */
    public function provideServiceContainerServices()
    {
        return array(
            array('configuration', ConfigurationInterface::class),
            array('connection_manager', ConnectionManagerInterface::class),
            array('event_dispatcher', EventDispatcherInterface::class),
            array('logger', LoggerInterface::class),
        );
    }
}
