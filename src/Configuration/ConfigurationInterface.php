<?php

namespace Accompli\Configuration;

/**
 * ConfigurationInterface.
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 */
interface ConfigurationInterface
{
    /**
     * Loads and validates the JSON configuration.
     *
     * @param string|null $configurationFile
     *
     * @throws RuntimeException
     */
    public function load($configurationFile = null);

    /**
     * Returns the configured hosts.
     *
     * @return Host[]
     */
    public function getHosts();

    /**
     * Returns the configured hosts for $stage.
     *
     * @param string $stage
     *
     * @return Host[]
     *
     * @throws UnexpectedValueException
     */
    public function getHostsByStage($stage);

    /**
     * Returns the configured event subscribers.
     *
     * @return array
     */
    public function getEventSubscribers();

    /**
     * Returns the configured event listeners.
     *
     * @return array
     */
    public function getEventListeners();
}
