<?php

namespace Accompli\Deployment\Connection;

/**
 * LocalConnectionAdapter
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class LocalConnectionAdapter implements ConnectionAdapterInterface
{
    /**
     * {@inheritDoc}
     */
    public function connect()
    {
        return true; // Does nothing
    }

    /**
     * {@inheritDoc}
     */
    public function executeCommand($command)
    {
        // Implement process component
    }

    /**
     * {@inheritDoc}
     */
    public function getContents($filename)
    {
        return file_get_contents($filename);
    }

    /**
     * {@inheritDoc}
     */
    public function putContents($destinationFilename, $data)
    {
        $result = file_put_conents($destinationFilename, $data);

        return ($result !== false);
    }

    /**
     * {@inheritDoc}
     */
    public function putFile($sourceFilename, $destinationFilename)
    {
        $result = copy($sourceFilename, $destinationFilename);

        return $result;
    }
}
