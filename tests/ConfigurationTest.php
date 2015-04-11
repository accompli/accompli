<?php

namespace Accompli\Test;

use Accompli\Configuration;
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
}
