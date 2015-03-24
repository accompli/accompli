<?php

namespace Accompli\Deployment;

/**
 * Release
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 * @package Accompli\Deployment
 **/
class Release
{
    /**
     * The unique identifier identifing this Release
     *
     * @access private
     * @var    string
     **/
    private $identifier;

    /**
     * __construct
     *
     * Constructs a new Release instance
     *
     * @access public
     * @param  string $identifier
     * @return null
     **/
    public function __construct($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * getIdenfifier
     *
     * Returns the release identifier
     *
     * @access public
     * @return string
     **/
    public function getIdenfifier()
    {
        return $this->identifier;
    }
}
