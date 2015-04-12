<?php

namespace Accompli\Test;

use Accompli\Configuration;
use Accompli\ConfigurationInterface;
use PHPUnit_Framework_TestCase;

/**
 * ConfigurationTest
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 * @package Accompli\Test
 */
class ConfigurationTest extends PHPUnit_Framework_TestCase
{
    /**
     * testLoadWithValidJSON
     *
     * @access public
     * @return null
     **/
    public function testLoadWithValidJSON()
    {
        $configuration = new Configuration();
        $configuration->load(__DIR__ . "/Resources/accompli.json");
    }

    /**
     * testLoadWithNonExistingJSONThrowsRuntimeException
     *
     * @expectedException RuntimeException
     *
     * @access public
     * @return null
     **/
    public function testLoadWithNonExistingJSONThrowsRuntimeException()
    {
        $configuration = new Configuration();
        $configuration->load(__DIR__ . "/Resources/accompli-non-existing.json");
    }

    /**
     * testLoadWithInvalidSyntaxJSONThrowsParsingException
     *
     * @expectedException Seld\JsonLint\ParsingException
     *
     * @access public
     * @return null
     **/
    public function testLoadWithInvalidSyntaxJSONThrowsParsingException()
    {
        $configuration = new Configuration();
        $configuration->load(__DIR__ . "/Resources/accompli-syntax-invalid.json");
    }

    /**
     * testLoadWithInvalidSchemaJSONThrowsJSONValidationException
     *
     * @expectedException Accompli\Exception\JSONValidationException
     *
     * @access public
     * @return null
     **/
    public function testLoadWithInvalidSchemaJSONThrowsJSONValidationException()
    {
        $configuration = new Configuration();
        $configuration->load(__DIR__ . "/Resources/accompli-schema-invalid.json");
    }

    /**
     * testGetHostsReturnsArrayWhenConfigurationNotLoaded
     *
     * @access public
     * @return null
     **/
    public function testGetHostsReturnsArrayWhenConfigurationNotLoaded()
    {
        $configuration = new Configuration();

        $this->assertInternalType("array", $configuration->getHosts() );
    }

    /**
     * testGetHostsReturnsArrayWhenConfigurationLoaded
     *
     * @access public
     * @return null
     **/
    public function testGetHostsReturnsArrayWhenConfigurationLoaded()
    {
        $configuration = new Configuration();
        $configuration->load(__DIR__ . "/Resources/accompli.json");

        $this->assertInternalType("array", $configuration->getHosts() );
    }

    /**
     * testGetHostsByStageThrowsUnexpectedValueExceptionOnInvalidStage
     *
     * @expectedException        UnexpectedValueException
     * @expectedExceptionMessage 'invalid' is not a valid stage.
     *
     * @access public
     * @return null
     **/
    public function testGetHostsByStageThrowsUnexpectedValueExceptionOnInvalidStage()
    {
        $configuration = new Configuration();
        $configuration->getHostsByStage("invalid");
    }

    /**
     * testGetHostsByStageReturnsEmptyArrayWhenConfigurationNotLoaded
     *
     * @access public
     * @return null
     **/
    public function testGetHostsByStageReturnsEmptyArrayWhenConfigurationNotLoaded()
    {
        $configuration = new Configuration();

        $result = $configuration->getHostsByStage(ConfigurationInterface::STAGE_TEST);
        $this->assertInternalType("array", $result);
        $this->assertCount(0, $result);
    }

    /**
     * testGetHostsByStageReturnsEmptyArrayWhenConfigurationLoadedWithNoHostsConfiguredForStage
     *
     * @access public
     * @return null
     **/
    public function testGetHostsByStageReturnsEmptyArrayWhenConfigurationLoadedWithNoHostsConfiguredForStage()
    {
        $configuration = new Configuration();
        $configuration->load(__DIR__ . "/Resources/accompli.json");

        $result = $configuration->getHostsByStage(ConfigurationInterface::STAGE_ACCEPTANCE);
        $this->assertInternalType("array", $result);
        $this->assertCount(0, $result);
    }

    /**
     * testGetHostsByStageReturnsArrayWhenConfigurationLoadedWithHostConfiguredForStage
     *
     * @access public
     * @return null
     **/
    public function testGetHostsByStageReturnsArrayWhenConfigurationLoadedWithHostConfiguredForStage()
    {
        $configuration = new Configuration();
        $configuration->load(__DIR__ . "/Resources/accompli.json");

        $result = $configuration->getHostsByStage(ConfigurationInterface::STAGE_TEST);
        $this->assertInternalType("array", $result);
        $this->assertCount(1, $result);
    }

    /**
     * testGetEventSubscribersReturnsEmptyArrayWhenConfigurationNotLoaded
     *
     * @access public
     * @return null
     **/
    public function testGetEventSubscribersReturnsEmptyArrayWhenConfigurationNotLoaded()
    {
        $configuration = new Configuration();

        $result = $configuration->getEventSubscribers();
        $this->assertInternalType("array", $result);
        $this->assertCount(0, $result);
    }

    /**
     * testGetEventSubscribersReturnsArrayWhenConfigurationLoaded
     *
     * @access public
     * @return null
     **/
    public function testGetEventSubscribersReturnsArrayWhenConfigurationLoaded()
    {
        $configuration = new Configuration();
        $configuration->load(__DIR__ . "/Resources/accompli.json");

        $result = $configuration->getEventSubscribers();
        $this->assertInternalType("array", $result);
        $this->assertCount(2, $result);
    }

    /**
     * testGetEventListenersReturnsEmptyArrayWhenConfigurationNotLoaded
     *
     * @access public
     * @return null
     **/
    public function testGetEventListenersReturnsEmptyArrayWhenConfigurationNotLoaded()
    {
        $configuration = new Configuration();

        $result = $configuration->getEventListeners();
        $this->assertInternalType("array", $result);
        $this->assertCount(0, $result);
    }

    /**
     * testGetEventListenersReturnsArrayWhenConfigurationLoaded
     *
     * @access public
     * @return null
     **/
    public function testGetEventListenersReturnsArrayWhenConfigurationLoaded()
    {
        $configuration = new Configuration();
        $configuration->load(__DIR__ . "/Resources/accompli.json");

        $result = $configuration->getEventListeners();
        $this->assertInternalType("array", $result);
        $this->assertCount(1, $result);
    }
}
