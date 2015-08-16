<?php

namespace Accompli\Console;

use Accompli\Accompli;
use Accompli\Console\Command\CreateReleaseCommand;
use Accompli\Console\Command\DeployReleaseCommand;
use Accompli\Console\Command\StatusCommand;
use Symfony\Component\Console\Application as BaseApplication;

/**
 * Application
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 * @package Accompli\Console
 **/
class Application extends BaseApplication
{
    /**
     * __construct
     *
     * Constructs a new Application instance
     *
     * @access public
     * @return null
     **/
    public function __construct()
    {
        parent::__construct('Accompli', Accompli::VERSION);
    }

    /**
     * getHelp
     *
     * Returns the help message
     *
     * @access public
     * @return string
     **/
    public function getHelp()
    {
        return Accompli::LOGO . parent::getHelp();
    }

    /**
     * getDefaultCommands
     *
     * Returns the array with default commands
     *
     * @access protected
     * @return array
     **/
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new StatusCommand();
        $commands[] = new CreateReleaseCommand();
        $commands[] = new DeployReleaseCommand();

        return $commands;
    }
}
