<?php

namespace Accompli\Configuration;

use Accompli\Deployment\Host;
use Accompli\Exception\JSONValidationException;
use JsonSchema\Validator;
use Nijens\Utilities\ObjectFactory;
use RuntimeException;
use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;
use UnexpectedValueException;

/**
 * Configuration.
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * The location of the configuration file.
     *
     * @var string
     */
    private $configurationFile;

    /**
     * The location of the configuration validation schema file.
     *
     * @var string
     */
    private $configurationSchema;

    /**
     * The array with configuration data.
     *
     * @var array
     */
    private $configuration = array();

    /**
     * The array with Host instances.
     *
     * @var Host[]
     */
    private $hosts = array();

    /**
     * Constructs a new Configuration instance.
     *
     * @param string|null $configurationFile
     * @param string|null $configurationSchema
     */
    public function __construct($configurationFile = null, $configurationSchema = null)
    {
        $this->configurationFile = $configurationFile;

        if (empty($configurationSchema)) {
            $configurationSchema = __DIR__.'/../Resources/accompli-schema.json';
        }

        $this->configurationSchema = $configurationSchema;
    }

    /**
     * Loads and validates the JSON configuration.
     *
     * @param string|null $configurationFile
     *
     * @throws RuntimeException
     *
     * @todo   Refactor
     */
    public function load($configurationFile = null)
    {
        if (isset($configurationFile)) {
            $this->configurationFile = $configurationFile;
        }

        $json = @file_get_contents($this->configurationFile);
        if ($json !== false) {
            $this->validateSyntax($json);
            $this->validateSchema($json);

            $this->hosts = array();
            $this->configuration = json_decode($json, true);
            if (isset($this->configuration['$extend'])) {
                $extendConfigurationFile = sprintf('%s/%s', dirname($this->configurationFile), $this->configuration['$extend']);
                unset($this->configuration['$extend']);

                $parentConfiguration = new static($extendConfigurationFile, $this->configurationSchema);
                $parentConfiguration->load();

                $this->configuration = array_merge_recursive($parentConfiguration->configuration, $this->configuration);
            }

            if (isset($this->configuration['events']['subscribers'])) {
                foreach ($this->configuration['events']['subscribers'] as $i => $subscriber) {
                    if (is_string($subscriber)) {
                        $this->configuration['events']['subscribers'][$i] = array('class' => $subscriber);
                    }
                }
            }

            return;
        }

        throw new RuntimeException(sprintf("'%s' could not be read.", $this->configurationFile));
    }

    /**
     * Validates the syntax of $json.
     *
     * @param string $json
     *
     * @throws ParsingException
     */
    private function validateSyntax($json)
    {
        $parser = new JsonParser();
        $result = $parser->lint($json);
        if ($result === null) {
            return;
        }

        throw new ParsingException(sprintf("'%s' does not contain valid JSON.\n%s", $this->configurationFile, $result->getMessage()), $result->getDetails());
    }

    /**
     * Validates the $json content with the JSON schema.
     *
     * @param string $json
     *
     * @return bool
     *
     * @throws JSONValidationException
     */
    private function validateSchema($json)
    {
        $jsonData = json_decode($json);
        $schemaData = json_decode(file_get_contents($this->configurationSchema));

        $validator = new Validator();
        $validator->check($jsonData, $schemaData);
        if ($validator->isValid() === false) {
            $errors = array();
            foreach ($validator->getErrors() as $error) {
                $errorMessage = $error['message'];
                if (isset($error['property'])) {
                    $errorMessage = $error['property'].' : '.$errorMessage;
                }
                $errors[] = $errorMessage;
            }

            throw new JSONValidationException(sprintf("'%s' does not match the expected JSON schema.", $this->configurationFile), $errors);
        }

        return true;
    }

    /**
     * Returns the configured hosts.
     *
     * @return Host[]
     */
    public function getHosts()
    {
        if (empty($this->hosts) && isset($this->configuration['hosts'])) {
            foreach ($this->configuration['hosts'] as $host) {
                $this->hosts[] = ObjectFactory::getInstance()->newInstance('Accompli\Deployment\Host', $host);
            }
        }

        return $this->hosts;
    }

    /**
     * Returns the configured hosts for $stage.
     *
     * @param string $stage
     *
     * @return Host[]
     *
     * @throws UnexpectedValueException when $stage is not a valid type
     */
    public function getHostsByStage($stage)
    {
        if (Host::isValidStage($stage) === false) {
            throw new UnexpectedValueException(sprintf("'%s' is not a valid stage.", $stage));
        }

        $hosts = array();
        foreach ($this->getHosts() as $host) {
            if ($host->getStage() === $stage) {
                $hosts[] = $host;
            }
        }

        return $hosts;
    }

    /**
     * Returns the configured event subscribers.
     *
     * @return array
     */
    public function getEventSubscribers()
    {
        if (isset($this->configuration['events']['subscribers'])) {
            return $this->configuration['events']['subscribers'];
        }

        return array();
    }

    /**
     * getEventListeners.
     *
     * Returns the configured event listeners
     *
     * @return array
     */
    public function getEventListeners()
    {
        if (isset($this->configuration['events']['listeners'])) {
            return $this->configuration['events']['listeners'];
        }

        return array();
    }

    /**
     * Returns the entire configuration as array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->configuration;
    }
}
