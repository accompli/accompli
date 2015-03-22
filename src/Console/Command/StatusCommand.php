<?php

namespace Accompli\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * StatusCommand
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 * @package Accompli\Console\Command
 * */
class StatusCommand extends Command
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
            ->setName("status")
            ->setDescription("Displays the deployment status of all configured hosts.");
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
