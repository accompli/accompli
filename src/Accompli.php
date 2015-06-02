<?php

namespace Accompli;

use Accompli\Event\InstallReleaseEvent;
use Accompli\Event\PrepareReleaseEvent;
use Accompli\Event\PrepareWorkspaceEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Accompli
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 * @package Accompli
 **/
class Accompli extends EventDispatcher
{
    /**
     * The Accompli CLI text logo
     *
     * @var string
     **/
    const LOGO = "
     _                                 _ _
    / \   ___ ___ ___  _ __ ___  _ __ | (_)
   / _ \ / __/ __/ _ \| '_ ` _ \| '_ \| | |
  / ___ \ (_| (_| (_) | | | | | | |_) | | |
 /_/   \_\___\___\___/|_| |_| |_| .__/|_|_|
 C'est fini. Accompli!          |_|
";

    /**
     * The Accompli CLI slogan text
     *
     * @var string
     **/
    const SLOGAN = "C'est fini. Accompli!";

    /**
     * The Accompli version
     *
     * @var string
     **/
    const VERSION = "0.1";

    /**
     * The configuration instance
     *
     * @access private
     * @var    ConfigurationInterface
     **/
    private $configuration;

    /**
     * __construct
     *
     * Constructs a new Accompli instance
     *
     * @access public
     * @param  ConfigurationInterface $configuration
     * @return null
     **/
    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * getConfiguration
     *
     * Returns the configuration instance
     *
     * @access public
     * @return ConfigurationInterface
     **/
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * createRelease
     *
     * Dispatches release creation events
     *
     * @access public
     * @return null
     * @todo   Add DeploymentAdapter (connection)
     **/
    public function createRelease()
    {
        $prepareWorkspaceEvent = new PrepareWorkspaceEvent($this);
        $this->dispatch(AccompliEvents::PREPARE_WORKSPACE, $prepareWorkspaceEvent);

        $prepareReleaseEvent = new PrepareReleaseEvent($this);
        $this->dispatch(AccompliEvents::PREPARE_RELEASE, $prepareReleaseEvent);

        $installReleaseEvent = new InstallReleaseEvent($this);
        $this->dispatch(AccompliEvents::INSTALL_RELEASE, $installReleaseEvent);
    }
}
