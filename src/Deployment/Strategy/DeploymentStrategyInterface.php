<?php

namespace Accompli\Deployment\Strategy;

/**
 * DeploymentStrategyInterface.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
interface DeploymentStrategyInterface
{
    /**
     * Installs a release according to the deployment strategy.
     *
     * @param string      $version
     * @param string|null $stage
     *
     * @return bool
     */
    public function install($version, $stage = null);

    /**
     * Deploys a release according to the deployment strategy.
     *
     * @param string $version
     * @param string $stage
     *
     * @return bool
     */
    public function deploy($version, $stage);
}
