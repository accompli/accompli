<?php

namespace Accompli\DependencyInjection;

use Accompli\Configuration\ConfigurationInterface;

/**
 * ConfigurationAwareInterface.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
interface ConfigurationAwareInterface
{
    /**
     * Sets the Accompli configuration.
     *
     * @param ConfigurationInterface $configuration
     */
    public function setConfiguration(ConfigurationInterface $configuration);
}
