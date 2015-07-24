<?php

namespace Accompli\Deployment;

use UnexpectedValueException;

/**
 * Host
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 * @package Accompli\Deployment
 */
class Host
{
    /**
     * The constant to identify a host in the test stage
     *
     * @var string
     **/
    const STAGE_TEST = 'test';

    /**
     * The constant to identify a host in the acceptance stage
     *
     * @var string
     **/
    const STAGE_ACCEPTANCE = 'acceptance';

    /**
     * The constant to identify a host in the production stage
     *
     * @var string
     **/
    const STAGE_PRODUCTION = 'production';

    /**
     * The stage (test, acceptance, production) of this host
     *
     * @access private
     * @var string
     **/
    private $stage;

    /**
     * The connection type for this host
     *
     * @access private
     * @var string
     **/
    private $connectionType;

    /**
     * The hostname of this host
     *
     * @access private
     * @var string|null
     **/
    private $hostname;

    /**
     * The base workspace path on this host
     *
     * @access private
     * @var string
     **/
    private $path;

    /**
     * __construct
     *
     * Constructs a new Host instance
     *
     * @access public
     * @param  string                   $stage
     * @param  string                   $connectionType
     * @param  string                   $hostname
     * @param  string                   $path
     * @return null
     * @throws UnexpectedValueException when $stage is not a valid type
     **/
    public function __construct($stage, $connectionType, $hostname, $path)
    {
        if (self::isValidStage($stage) === false) {
            throw new UnexpectedValueException(sprintf("'%s' is not a valid stage.", $stage));
        }

        $this->stage = $stage;
        $this->connectionType = $connectionType;
        $this->hostname = $hostname;
        $this->path = $path;
    }

    /**
     * getStage
     *
     * Returns the stage of this host
     *
     * @access public
     * @return string
     **/
    public function getStage()
    {
        return $this->stage;
    }

    /**
     * getConnectionType
     *
     * Returns the connection type of this host
     *
     * @access public
     * @return string
     **/
    public function getConnectionType()
    {
        return $this->connectionType;
    }

    /**
     * getHostname
     *
     * Returns the hostname of this host
     *
     * @access public
     * @return string
     **/
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * getPath
     *
     * Returns the base workspace path of this host
     *
     * @access public
     * @return string
     **/
    public function getPath()
    {
        return $this->path;
    }

    /**
     * isValidStage
     *
     * Returns true if $stage is a valid stage type
     *
     * @access public
     * @param  string  $stage
     * @return boolean
     **/
    public static function isValidStage($stage)
    {
        return in_array($stage, array(self::STAGE_TEST, self::STAGE_ACCEPTANCE, self::STAGE_PRODUCTION));
    }
}
