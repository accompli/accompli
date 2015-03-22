<?php

namespace Accompli\Event;

use Accompli\Accompli;
use Symfony\Component\EventDispatcher\Event;

/**
 * AbstractEvent
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 * @package Accompli\Event
 **/
abstract class AbstractEvent extends Event
{
    /**
     * The Accompli instance
     *
     * @access protected
     * @var    Accompli
     **/
    protected $accompli;

    /**
     * __construct
     *
     * Constructs a new AbstractEvent instance
     *
     * @access public
     * @param  Accompli $accompli
     * @return void
     **/
    public function __construct(Accompli $accompli)
    {
        $this->accompli = $accompli;
    }
}
