<?php

namespace Accompli\Console\Command;

use Accompli\Accompli;
use Accompli\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CreateReleaseCommand
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 * @package Accompli\Console\Command
 **/
class CreateReleaseCommand extends Command
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
            ->setName('create-release')
            ->setDescription('Creates a new release for deployment.')
            ->addArgument('version', InputArgument::REQUIRED, 'The version to create a release for.')
            ->addArgument('stage', InputArgument::OPTIONAL, 'The stage to select hosts for.')
            ->addOption('project-dir', null, InputOption::VALUE_OPTIONAL, 'The location of the project directory.', getcwd());
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
        $configuration = new Configuration();
        $configuration->load($input->getOption('project-dir') . DIRECTORY_SEPARATOR . 'accompli.json');

        $accompli = new Accompli($configuration);
        $accompli->initializeEventListeners();

        $hosts = $configuration->getHosts();
        if ($input->getArgument('stage') !== null) {
            $hosts = $configuration->getHostsByStage($input->getArgument('stage'));
        }

        foreach ($hosts as $host) {
            $accompli->createRelease($host);
        }
    }
}
