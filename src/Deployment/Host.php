<?php

namespace Accompli\Deployment;

use Accompli\Deployment\Connection\ConnectionAdapterInterface;
use UnexpectedValueException;

/**
 * Host.
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 */
class Host
{
    /**
     * The constant to identify a host in the test stage.
     *
     * @var string
     */
    const STAGE_TEST = 'test';

    /**
     * The constant to identify a host in the acceptance stage.
     *
     * @var string
     */
    const STAGE_ACCEPTANCE = 'acceptance';

    /**
     * The constant to identify a host in the production stage.
     *
     * @var string
     */
    const STAGE_PRODUCTION = 'production';

    /**
     * The stage (test, acceptance, production) of this host.
     *
     * @var string
     */
    private $stage;

    /**
     * The connection type for this host.
     *
     * @var string
     */
    private $connectionType;

    /**
     * The hostname of this host.
     *
     * @var string|null
     */
    private $hostname;

    /**
     * The base workspace path on this host.
     *
     * @var string
     */
    private $path;

    /**
     * The array with connection options.
     *
     * @var array
     */
    private $connectionOptions;

    /**
     * The connection instance used to connect to and communicate with this Host.
     *
     * @var ConnectionAdapterInterface
     */
    private $connection;

    /**
     * Constructs a new Host instance.
     *
     * @param string $stage
     * @param string $connectionType
     * @param string $hostname
     * @param string $path
     * @param array  $connectionOptions
     *
     * @throws UnexpectedValueException when $stage is not a valid type
     */
    public function __construct($stage, $connectionType, $hostname, $path, array $connectionOptions = array())
    {
        if (self::isValidStage($stage) === false) {
            throw new UnexpectedValueException(sprintf("'%s' is not a valid stage.", $stage));
        }

        $this->stage = $stage;
        $this->connectionType = $connectionType;
        $this->hostname = $hostname;
        $this->path = $path;
        $this->connectionOptions = $connectionOptions;
    }

    /**
     * Returns true if this Host has a connection instance.
     *
     * @return ConnectionAdapterInterface
     */
    public function hasConnection()
    {
        return ($this->connection instanceof ConnectionAdapterInterface);
    }

    /**
     * Returns the stage of this host.
     *
     * @return string
     */
    public function getStage()
    {
        return $this->stage;
    }

    /**
     * Returns the connection type of this host.
     *
     * @return string
     */
    public function getConnectionType()
    {
        return $this->connectionType;
    }

    /**
     * Returns the hostname of this host.
     *
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * Returns the connection instance.
     *
     * @return ConnectionAdapterInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Returns the base workspace path of this host.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Sets the connection instance.
     *
     * @param ConnectionAdapterInterface $connection
     */
    public function setConnection(ConnectionAdapterInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Returns true if $stage is a valid stage type.
     *
     * @param string $stage
     *
     * @return bool
     */
    public static function isValidStage($stage)
    {
        return in_array($stage, array(self::STAGE_TEST, self::STAGE_ACCEPTANCE, self::STAGE_PRODUCTION));
    }
}
