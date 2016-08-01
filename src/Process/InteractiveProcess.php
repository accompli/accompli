<?php

namespace Accompli\Process;

use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Pipes\PipesInterface;
use Symfony\Component\Process\Process;

/**
 * InteractiveProcess.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class InteractiveProcess extends Process
{
    /**
     * The timestamp of the last time the process produced output.
     *
     * @var float
     */
    protected $lastOutputTime;

    /**
     * The instance containing the pipes used by the process.
     *
     * @var PipesInterface
     */
    protected $processPipes;

    /**
     * The InputStream instance.
     *
     * @var InputStream
     */
    private $inputStream;

    /**
     * The output buffer.
     *
     * @var string
     */
    private $outputBuffer = '';

    /**
     * Constructs a new InteractiveProcess instance.
     *
     * @param string         $command The command line to run
     * @param string|null    $cwd     The working directory or null to use the working dir of the current PHP process
     * @param array|null     $env     The environment variables or null to use the same environment as the current PHP process
     * @param int|float|null $timeout The timeout in seconds or null to disable
     * @param array          $options An array of options for proc_open
     */
    public function __construct($command, $cwd = null, array $env = null, $timeout = 60, array $options = array())
    {
        $this->inputStream = new InputStream();

        parent::__construct($command, $cwd, $env, $this->inputStream, $timeout, $options);

        $this->setPty(true);
    }

    /**
     * Returns the output of the interactive process when there is a match for $expectRegex.
     *
     * @param string $expectRegex
     *
     * @return string
     */
    public function read($expectRegex)
    {
        $this->lastOutputTime = microtime(true);

        $output = '';
        while (true) {
            $this->checkTimeout();
            $this->readIntoOutputBuffer();

            $matches = array();
            if (preg_match($expectRegex, $this->outputBuffer, $matches) === 1) {
                $outputLength = strpos($this->outputBuffer, $matches[0]) + strlen($matches[0]);

                $output = substr($this->outputBuffer, 0, $outputLength);
                $this->outputBuffer = substr($this->outputBuffer, $outputLength);

                break;
            }
        }

        return $output;
    }

    /**
     * Writes a command into the interactive process.
     *
     * @param string $command
     */
    public function write($command)
    {
        $this->inputStream->write($command);

        if ($this->isStarted()) {
            $this->readIntoOutputBuffer();
        }
    }

    /**
     * Reads the output from the pipes of the process into the output buffer.
     */
    private function readIntoOutputBuffer()
    {
        $result = $this->processPipes->readAndWrite(true, false);
        foreach ($result as $type => $data) {
            if ($type !== 3) {
                $this->lastOutputTime = microtime(true);

                $this->outputBuffer .= $data;
            }
        }
    }
}
