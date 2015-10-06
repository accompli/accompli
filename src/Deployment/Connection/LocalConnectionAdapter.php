<?php

namespace Accompli\Deployment\Connection;

/**
 * LocalConnectionAdapter.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class LocalConnectionAdapter implements ConnectionAdapterInterface
{
    /**
     * {@inheritdoc}
     */
    public function connect()
    {
        return true; // Does nothing
    }

    /**
     * {@inheritdoc}
     */
    public function executeCommand($command)
    {
        // Implement process component
    }

    /**
     * {@inheritdoc}
     */
    public function getContents($filename)
    {
        return file_get_contents($filename);
    }

    /**
     * {@inheritdoc}
     */
    public function putContents($destinationFilename, $data)
    {
        $result = file_put_conents($destinationFilename, $data);

        return ($result !== false);
    }

    /**
     * {@inheritdoc}
     */
    public function putFile($sourceFilename, $destinationFilename)
    {
        $result = copy($sourceFilename, $destinationFilename);

        return $result;
    }
}
