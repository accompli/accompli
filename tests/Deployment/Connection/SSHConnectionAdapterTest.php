<?php

namespace Accompli\Test;

use Accompli\Deployment\Connection\SSHConnectionAdapter;
use Accompli\Test\Deployment\Connection\ConnectedConnectionAdapterTestCase;

/**
 * SSHConnectionAdapterTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class SSHConnectionAdapterTest extends ConnectedConnectionAdapterTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function createConnectionAdapter()
    {
        return new SSHConnectionAdapter('localhost', 'publickey');
    }
}
