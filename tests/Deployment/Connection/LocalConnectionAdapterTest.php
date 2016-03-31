<?php

namespace Accompli\Test\Deployment\Connection;

use Accompli\Deployment\Connection\LocalConnectionAdapter;

/**
 * LocalConnectionAdapterTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class LocalConnectionAdapterTest extends ConnectionAdapterTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function createConnectionAdapter()
    {
        return new LocalConnectionAdapter();
    }
}
