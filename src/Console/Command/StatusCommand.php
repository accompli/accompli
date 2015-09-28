<?php

namespace Accompli\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * StatusCommand.
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 */
class StatusCommand extends Command
{
    /**
     * Configures this command.
     */
    protected function configure()
    {
        $this
            ->setName('status')
            ->setDescription('Displays the deployment status of all configured hosts.');
    }

    /**
     * Executes this command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
    }
}
