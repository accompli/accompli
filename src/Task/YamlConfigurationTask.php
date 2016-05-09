<?php

namespace Accompli\Task;

use Accompli\AccompliEvents;
use Accompli\Deployment\Connection\ConnectionAdapterInterface;
use Accompli\Deployment\Release;
use Accompli\EventDispatcher\Event\InstallReleaseEvent;
use Accompli\EventDispatcher\Event\LogEvent;
use Accompli\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Yaml\Yaml;

/**
 * YamlConfigurationTask.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class YamlConfigurationTask extends AbstractConnectedTask
{
    /**
     * The location of the configuration file within a release.
     *
     * @var string
     */
    private $configurationFile;

    /**
     * The configuration to be configured within the configuration file.
     *
     * @var array
     */
    private $configuration;

    /**
     * The stage-based configurations to be configured within the configuration file.
     *
     * @var array
     */
    private $stageSpecificConfigurations;

    /**
     * The array of array paths to keys within the configuration that should have a generated hash.
     *
     * @var array
     */
    private $generateValueForParameters;

    /**
     * The array with environment variables to use in the YAML configuration.
     *
     * @var array
     */
    private $environmentVariables;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            AccompliEvents::INSTALL_RELEASE => array(
                array('onInstallReleaseCreateOrUpdateConfiguration', 10),
            ),
        );
    }

    /**
     * Constructs a new YamlConfigurationTask instance.
     *
     * @param string $configurationFile
     * @param array  $configuration
     * @param array  $stageSpecificConfigurations
     * @param array  $generateValueForParameters
     */
    public function __construct($configurationFile, array $configuration = array(), array $stageSpecificConfigurations = array(), array $generateValueForParameters = array())
    {
        $this->configurationFile = $configurationFile;
        $this->configuration = $configuration;
        $this->stageSpecificConfigurations = $stageSpecificConfigurations;
        $this->generateValueForParameters = $generateValueForParameters;
    }

    /**
     * Saves a YAML configuration file to a path within the release.
     *
     * @param InstallReleaseEvent      $event
     * @param string                   $eventName
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function onInstallReleaseCreateOrUpdateConfiguration(InstallReleaseEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        $release = $event->getRelease();
        $this->gatherEnvironmentVariables($release);

        $connection = $this->ensureConnection($release->getWorkspace()->getHost());

        $configurationFile = $release->getPath().$this->configurationFile;
        $configurationDistributionFile = $configurationFile.'.dist';

        $context = array('action' => 'Creating', 'configurationFile' => $configurationFile, 'event.task.action' => TaskInterface::ACTION_IN_PROGRESS);
        if ($connection->isFile($configurationFile)) {
            $context['action'] = 'Updating';
        }

        $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::NOTICE, '{action} configuration file "{configurationFile}"...', $eventName, $this, $context));

        $yamlConfiguration = $this->getYamlConfiguration($connection, $release->getWorkspace()->getHost()->getStage(), $configurationFile, $configurationDistributionFile);
        if ($connection->putContents($configurationFile, $yamlConfiguration)) {
            $context['event.task.action'] = TaskInterface::ACTION_COMPLETED;
            if ($context['action'] === 'Creating') {
                $context['action'] = 'Created';
            } else {
                $context['action'] = 'Updated';
            }

            $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::NOTICE, '{action} configuration file "{configurationFile}".', $eventName, $this, $context));
        } else {
            $context['event.task.action'] = TaskInterface::ACTION_FAILED;
            $context['action'] = strtolower($context['action']);

            $eventDispatcher->dispatch(AccompliEvents::LOG, new LogEvent(LogLevel::WARNING, 'Failed {action} configuration file "{configurationFile}".', $eventName, $this, $context));
        }
    }

    /**
     * Gathers environment variables to use in the YAML configuration.
     *
     * @param Release $release
     */
    private function gatherEnvironmentVariables(Release $release)
    {
        $this->environmentVariables = array(
            '%stage%' => $release->getWorkspace()->getHost()->getStage(),
            '%version%' => $release->getVersion(),
        );
    }

    /**
     * Returns the generated YAML content based on the existing configuration file, distribution configuration file and the configuration configured with this task.
     *
     * @param ConnectionAdapterInterface $connection
     * @param string                     $stage
     * @param string                     $configurationFile
     * @param string                     $configurationDistributionFile
     *
     * @return string
     */
    private function getYamlConfiguration(ConnectionAdapterInterface $connection, $stage, $configurationFile, $configurationDistributionFile)
    {
        $configuration = array();
        if ($connection->isFile($configurationFile)) {
            $configuration = Yaml::parse($connection->getContents($configurationFile));
        }

        $distributionConfiguration = array();
        if ($connection->isFile($configurationDistributionFile)) {
            $distributionConfiguration = Yaml::parse($connection->getContents($configurationDistributionFile));
        }

        $stageSpecificConfiguration = array();
        if (isset($this->stageSpecificConfigurations[$stage])) {
            $stageSpecificConfiguration = $this->stageSpecificConfigurations[$stage];
        }

        $configuration = array_replace_recursive($distributionConfiguration, $configuration, $this->configuration, $stageSpecificConfiguration);
        foreach ($this->generateValueForParameters as $generateValueForParameter) {
            $this->findKeyAndGenerateValue($configuration, explode('.', $generateValueForParameter));
        }
        $configuration = $this->addEnvironmentVariables($configuration);

        return Yaml::dump($configuration);
    }

    /**
     * Traverses through the configuration array to find the configuration key and generates a unique sha1 hash.
     *
     * @param array $configuration
     * @param array $parameterParts
     */
    private function findKeyAndGenerateValue(array &$configuration, array $parameterParts)
    {
        foreach ($configuration as $key => $value) {
            if ($key === current($parameterParts)) {
                if (is_array($value) && count($parameterParts) > 1) {
                    array_shift($parameterParts);
                    $this->findKeyAndGenerateValue($value, $parameterParts);

                    $configuration[$key] = $value;
                } elseif (is_scalar($value)) {
                    $configuration[$key] = sha1(uniqid());
                }
            }
        }
    }

    /**
     * Adds the environment variables.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    private function addEnvironmentVariables($value)
    {
        if (is_array($value)) {
            $value = array_map(array($this, __METHOD__), $value);
        } elseif (is_string($value)) {
            $value = strtr($value, $this->environmentVariables);
        }

        return $value;
    }
}
