<?php

namespace Accompli\Test\Configuration;

use Accompli\Accompli;
use Accompli\Configuration\Configuration;
use Accompli\Deployment\Host;
use Nijens\ProtocolStream\Stream\Stream;
use Nijens\ProtocolStream\StreamManager;
use PHPUnit_Framework_TestCase;
use UnexpectedValueException;

/**
 * ConfigurationTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class ConfigurationTest extends PHPUnit_Framework_TestCase
{
    /**
     * Unregisters the accompli stream wrapper.
     */
    public function tearDown()
    {
        StreamManager::create()->unregisterStream('accompli');
    }

    /**
     * testLoadWithValidJSON.
     */
    public function testLoadWithValidJSON()
    {
        $configuration = new Configuration();
        $configuration->load(__DIR__.'/../Resources/accompli.json');
    }

    /**
     * testLoadWithNonExistingJSONThrowsRuntimeException.
     *
     * @expectedException InvalidArgumentException
     */
    public function testLoadWithNonExistingJSONThrowsRuntimeException()
    {
        $configuration = new Configuration();
        $configuration->load(__DIR__.'/../Resources/accompli-non-existing.json');
    }

    /**
     * testLoadWithInvalidSyntaxJSONThrowsParsingException.
     *
     * @expectedException Seld\JsonLint\ParsingException
     */
    public function testLoadWithInvalidSyntaxJSONThrowsParsingException()
    {
        $configuration = new Configuration();
        $configuration->load(__DIR__.'/../Resources/accompli-syntax-invalid.json');
    }

    /**
     * testLoadWithInvalidSchemaJSONThrowsJSONValidationException.
     *
     * @expectedException Accompli\Exception\JSONValidationException
     */
    public function testLoadWithInvalidSchemaJSONThrowsJSONValidationException()
    {
        $configuration = new Configuration();
        $configuration->load(__DIR__.'/../Resources/accompli-schema-invalid.json');
    }

    /**
     * Tests if Configuration::load imports the configuration extend.
     */
    public function testLoadWithExtendedConfiguration()
    {
        $configuration = new Configuration();
        $configuration->load(__DIR__.'/../Resources/accompli-extended.json');

        $this->assertArrayHasKey('deployment', $configuration->toArray());
    }

    /**
     * Tests if Configuration::load imports the configuration extend through the accompli stream wrapper.
     */
    public function testLoadWithExtendedConfigurationFromRecipe()
    {
        $stream = new Stream('accompli', array(
                'recipe' => realpath(__DIR__.'/../../src/Resources/recipe'),
            ), false);

        StreamManager::create()->registerStream($stream);

        $configuration = new Configuration();
        $configuration->load(__DIR__.'/../Resources/accompli-extended-recipe.json');
    }

    /**
     * Tests if Configuration::load first imports the configuration extend before validating the JSON schema.
     */
    public function testLoadWithEmptyExtendedConfiguration()
    {
        $configuration = new Configuration();
        $configuration->load(__DIR__.'/../Resources/accompli-empty-extended.json');
    }

    /**
     * testGetHostsReturnsArrayWhenConfigurationNotLoaded.
     */
    public function testGetHostsReturnsArrayWhenConfigurationNotLoaded()
    {
        $configuration = new Configuration();

        $this->assertInternalType('array', $configuration->getHosts());
    }

    /**
     * testGetHostsReturnsArrayWhenConfigurationLoaded.
     */
    public function testGetHostsReturnsArrayWhenConfigurationLoaded()
    {
        $configuration = new Configuration();
        $configuration->load(__DIR__.'/../Resources/accompli.json');

        $this->assertInternalType('array', $configuration->getHosts());
        $this->assertNotEmpty($configuration->getHosts());
        $this->assertInstanceOf(Host::class, current($configuration->getHosts()));
    }

    /**
     * testGetHostsByStageThrowsUnexpectedValueExceptionOnInvalidStage.
     *
     * @expectedException        UnexpectedValueException
     * @expectedExceptionMessage 'invalid' is not a valid stage.
     */
    public function testGetHostsByStageThrowsUnexpectedValueExceptionOnInvalidStage()
    {
        $configuration = new Configuration();
        $configuration->getHostsByStage('invalid');
    }

    /**
     * testGetHostsByStageReturnsEmptyArrayWhenConfigurationNotLoaded.
     */
    public function testGetHostsByStageReturnsEmptyArrayWhenConfigurationNotLoaded()
    {
        $configuration = new Configuration();

        $result = $configuration->getHostsByStage(Host::STAGE_TEST);
        $this->assertInternalType('array', $result);
        $this->assertCount(0, $result);
    }

    /**
     * testGetHostsByStageReturnsEmptyArrayWhenConfigurationLoadedWithNoHostsConfiguredForStage.
     */
    public function testGetHostsByStageReturnsEmptyArrayWhenConfigurationLoadedWithNoHostsConfiguredForStage()
    {
        $configuration = new Configuration();
        $configuration->load(__DIR__.'/../Resources/accompli.json');

        $result = $configuration->getHostsByStage(Host::STAGE_ACCEPTANCE);
        $this->assertInternalType('array', $result);
        $this->assertCount(0, $result);
    }

    /**
     * testGetHostsByStageReturnsArrayWhenConfigurationLoadedWithHostConfiguredForStage.
     */
    public function testGetHostsByStageReturnsArrayWhenConfigurationLoadedWithHostConfiguredForStage()
    {
        $configuration = new Configuration();
        $configuration->load(__DIR__.'/../Resources/accompli.json');

        $this->assertInternalType('array', $configuration->getHostsByStage(Host::STAGE_TEST));
        $this->assertNotEmpty($configuration->getHostsByStage(Host::STAGE_TEST));
        $this->assertInstanceOf(Host::class, current($configuration->getHostsByStage(Host::STAGE_TEST)));
    }

    /**
     * testGetEventSubscribersReturnsEmptyArrayWhenConfigurationNotLoaded.
     */
    public function testGetEventSubscribersReturnsEmptyArrayWhenConfigurationNotLoaded()
    {
        $configuration = new Configuration();

        $result = $configuration->getEventSubscribers();
        $this->assertInternalType('array', $result);
        $this->assertCount(0, $result);
    }

    /**
     * testGetEventSubscribersReturnsArrayWhenConfigurationLoaded.
     */
    public function testGetEventSubscribersReturnsArrayWhenConfigurationLoaded()
    {
        $configuration = new Configuration();
        $configuration->load(__DIR__.'/../Resources/accompli.json');

        $result = $configuration->getEventSubscribers();
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
    }

    /**
     * testGetEventSubscribersAlwaysReturnsArrayOfArraysWithClassKeyWhenConfigurationLoaded.
     *
     * @depends testGetEventSubscribersReturnsArrayWhenConfigurationLoaded
     */
    public function testGetEventSubscribersAlwaysReturnsArrayOfArraysWithClassKeyWhenConfigurationLoaded()
    {
        $configuration = new Configuration();
        $configuration->load(__DIR__.'/../Resources/accompli.json');

        $result = $configuration->getEventSubscribers();
        foreach ($result as $resultItem) {
            $this->assertArrayHasKey('class', $resultItem);
        }
    }

    /**
     * testGetEventListenersReturnsEmptyArrayWhenConfigurationNotLoaded.
     */
    public function testGetEventListenersReturnsEmptyArrayWhenConfigurationNotLoaded()
    {
        $configuration = new Configuration();

        $result = $configuration->getEventListeners();
        $this->assertInternalType('array', $result);
        $this->assertCount(0, $result);
    }

    /**
     * testGetEventListenersReturnsArrayWhenConfigurationLoaded.
     */
    public function testGetEventListenersReturnsArrayWhenConfigurationLoaded()
    {
        $configuration = new Configuration();
        $configuration->load(__DIR__.'/../Resources/accompli.json');

        $result = $configuration->getEventListeners();
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
    }

    /**
     * Tests if Configuration::getDeploymentStrategyClass returns null when the configuration is not loaded.
     */
    public function testGetDeploymentStrategyClassReturnsNullWhenConfigurationNotLoaded()
    {
        $configuration = new Configuration();

        $this->assertNull($configuration->getDeploymentStrategyClass());
    }

    /**
     * Tests if Configuration::getDeploymentStrategyClass returns a valid classname when the configuration is loaded.
     */
    public function testGetDeploymentStrategyClassReturnsValidClassName()
    {
        $configuration = new Configuration();
        $configuration->load(__DIR__.'/../Resources/accompli-extended.json');

        $this->assertInternalType('string', $configuration->getDeploymentStrategyClass());
        $this->assertTrue(class_exists($configuration->getDeploymentStrategyClass()));
    }

    /**
     * Tests if Configuration::getDeploymentConnectionClasses returns an empty array when the configuration is not loaded.
     */
    public function testGetDeploymentDeploymentConnectionClassesReturnsEmptyArrayWhenConfigurationNotLoaded()
    {
        $configuration = new Configuration();

        $this->assertInternalType('array', $configuration->getDeploymentConnectionClasses());
        $this->assertEmpty($configuration->getDeploymentConnectionClasses());
    }

    /**
     * Tests if Configuration::getDeploymentConnectionClasses returns a filled array with at least the 'local' key.
     */
    public function testGetDeploymentDeploymentConnectionClassesReturnsArray()
    {
        $configuration = new Configuration();
        $configuration->load(__DIR__.'/../Resources/accompli-extended.json');

        $this->assertInternalType('array', $configuration->getDeploymentConnectionClasses());
        $this->assertArrayHasKey('local', $configuration->getDeploymentConnectionClasses());
    }
}
