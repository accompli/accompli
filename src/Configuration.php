<?php

namespace Accompli;

use Accompli\Exception\JSONValidationException;
use JsonSchema\Validator;
use RuntimeException;
use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;
use UnexpectedValueException;

/**
 * Configuration
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 * @package Accompli
 **/
class Configuration implements ConfigurationInterface
{
    /**
     * The location of the configuration file
     *
     * @access private
     * @var    string
     **/
    private $configurationFile;

    /**
     * The location of the configuration validation schema file
     *
     * @access private
     * @var    string
     **/
    private $configurationSchema;

    /**
     * The array with configuration data
     *
     * @access private
     * @var    array
     **/
    private $configuration = array();

    /**
     * __construct
     *
     * Constructs a new Configuration instance
     *
     * @access public
     * @param  string|null $configurationFile
     * @param  string|null $configurationSchema
     * @return null
     **/
    public function __construct($configurationFile = null, $configurationSchema = null)
    {
        $this->configurationFile = $configurationFile;

        if (empty($configurationSchema) ) {
            $configurationSchema = __DIR__ . "/Resources/accompli-schema.json";
        }

        $this->configurationSchema = $configurationSchema;
    }

    /**
     * load
     *
     * Loads and validates the JSON configuration
     *
     * @access public
     * @param  string|null $configurationFile
     * @return null
     * @throws RuntimeException
     **/
    public function load($configurationFile = null)
    {
        if (isset($configurationFile) ) {
            $this->configurationFile = $configurationFile;
        }

        $json = @file_get_contents($this->configurationFile);
        if ($json !== false) {
            $this->validateSyntax($json);
            $this->validateSchema($json);

            $this->data = json_decode($json, true);

            return;
        }

        throw new RuntimeException("'" . $this->configurationFile . "' could not be read.");
    }

    /**
     * validateSyntax
     *
     * Validates the syntax of $json
     *
     * @access private
     * @param  string $json
     * @return boolean
     * @throws UnexpectedValueException
     * @throws ParsingException
     **/
    private function validateSyntax($json)
    {
        $parser = new JsonParser();
        $result = $parser->lint($json);
        if (null === $result) {
            if (defined("JSON_ERROR_UTF8") && JSON_ERROR_UTF8 === json_last_error() ) {
                throw new UnexpectedValueException("'" . $this->configurationFile . "' is not UTF-8, could not parse as JSON.");
            }

            return true;
        }

        throw new ParsingException("'" . $this->configurationFile . "' does not contain valid JSON.\n" . $result->getMessage(), $result->getDetails() );
    }

    /**
     * validateSchema
     *
     * Validates the $json content with the JSON schema
     *
     * @access private
     * @param  string  $json
     * @return boolean
     * @throws JSONValidationException
     **/
    private function validateSchema($json)
    {
        $jsonData = json_decode($json);
        $schemaData = json_decode(file_get_contents($this->configurationSchema) );

        $validator = new Validator();
        $validator->check($jsonData, $schemaData);
        if ($validator->isValid() === false) {
            $errors = array();
            foreach ($validator->getErrors() as $error) {
                $errors[] = ($error["property"] ? $error["property"] . " : " : "") . $error["message"];
            }

            throw new JSONValidationException("'" . $this->configurationFile . "' does not match the expected JSON schema.", $errors);
        }

        return true;
    }

    /**
     * getHosts
     *
     * Returns the configured hosts
     *
     * @access public
     * @return array
     **/
    public function getHosts()
    {
        return $this->configuration["hosts"];
    }

    /**
     * getHostsByStage
     *
     * Returns the configured hosts for $stage
     *
     * @access public
     * @param  string $stage
     * @return array
     * @throws UnexpectedValueException
     **/
    public function getHostsByStage($stage)
    {
        if (in_array($stage, array("test", "acceptance", "production") ) === false) {
            throw new UnexpectedValueException("'" . $stage . "' is not a valid stage.");
        }

        $hosts = array();
        foreach ($this->configuration["host"] as $host) {
            if ($host["stage"] === $stage) {
                $hosts[] = $host;
            }
        }

        return $hosts;
    }

    /**
     * getEventSubscribers
     *
     * Returns the configured event subscribers
     *
     * @access public
     * @return array
     **/
    public function getEventSubscribers()
    {
        if (isset($this->configuration["events"]["subscribers"]) ) {
            return $this->configuration["events"]["subscribers"];
        }

        return array();
    }

    /**
     * getEventListeners
     *
     * Returns the configured event listeners
     *
     * @access public
     * @return array
     **/
    public function getEventListeners()
    {
        if (isset($this->configuration["events"]["listeners"]) ) {
            return $this->configuration["events"]["listeners"];
        }

        return array();
    }
}
