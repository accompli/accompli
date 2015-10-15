<?php

namespace Accompli\Test;

use Accompli\Deployment\Connection\SSHConnectionAdapter;
use Accompli\Test\Deployment\Connection\ConnectionAdapterTestCase;

/**
 * SSHConnectionAdapterTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class SSHConnectionAdapterTest extends ConnectionAdapterTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function createConnectionAdapter()
    {
        return new SSHConnectionAdapter('localhost', 'publickey');
    }
}
