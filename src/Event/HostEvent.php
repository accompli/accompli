<?php

namespace Accompli\Event;

use Accompli\Deployment\Host;
use Symfony\Component\EventDispatcher\Event;

/**
 * HostEvent.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class HostEvent extends Event
{
    /**
     * The Host instance.
     *
     * @var Host
     */
    private $host;

    /**
     * Constructs a new HostEvent.
     *
     * @param Host $host
     */
    public function __construct(Host $host)
    {
        $this->host = $host;
    }

    /**
     * Returns the Host instance.
     *
     * @return Host
     */
    public function getHost()
    {
        return $this->host;
    }
}
