<?php

namespace Accompli;

/**
 * ConfigurationInterface
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 * @package Accompli
 **/
interface ConfigurationInterface
{
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
    public function load($configurationFile = null);

    /**
     * getHosts
     *
     * Returns the configured hosts
     *
     * @access public
     * @return array
     **/
    public function getHosts();

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
    public function getHostsByStage($stage);

    /**
     * getEventSubscribers
     *
     * Returns the configured event subscribers
     *
     * @access public
     * @return array
     **/
    public function getEventSubscribers();

    /**
     * getEventListeners
     *
     * Returns the configured event listeners
     *
     * @access public
     * @return array
     **/
    public function getEventListeners();
}
