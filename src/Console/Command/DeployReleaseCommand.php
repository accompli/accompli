<?php

namespace Accompli\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * DeployReleaseCommand.
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 */
class DeployReleaseCommand extends Command
{
    /**
     * Configures this command
     */
    protected function configure()
    {
        $this
            ->setName('deploy-release')
            ->setDescription('Deploys a release to all configured hosts of a stage.')
            ->addArgument('version', InputArgument::REQUIRED, 'The version to deploy.')
            ->addArgument('stage', InputArgument::REQUIRED, 'The stage to select hosts for.');
    }

    /**
     * Executes this command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
    }
}
