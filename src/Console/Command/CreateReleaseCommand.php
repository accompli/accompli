<?php

namespace Accompli\Console\Command;

use Symfony\Component\Console\Command\Command;

/**
 * CreateReleaseCommand
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 * @package Accompli\Console\Command
 * */
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
            ->setName("release:create")
            ->setDescription("Creates a new release for deployment.");
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
