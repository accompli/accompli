<?php

namespace Accompli\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * DeployReleaseCommand
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 * @package Accompli\Console\Command
 **/
class DeployReleaseCommand extends Command
{
    /**
     * configure
     *
     * Configures this command
     *
     * @access protected
     * @return null
     **/
    protected function configure()
    {
        $this
            ->setName("release:deploy")
            ->setDescription("Deploys a release to all configured hosts of a stage.")
            ->addArgument("release", InputArgument::REQUIRED, "The release number to deploy.")
            ->addArgument("stage", InputArgument::REQUIRED, "The stage to deploy to.");
    }

    /**
     * execute
     *
     * Executes this command
     *
     * @access protected
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return null
     **/
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
    }
}
