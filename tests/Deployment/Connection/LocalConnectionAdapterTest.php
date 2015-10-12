<?php

namespace Accompli\Test;

use Accompli\Deployment\Connection\LocalConnectionAdapter;
use Accompli\Test\Deployment\Connection\ConnectionAdapterTestCase;

/**
 * LocalConnectionAdapterTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class LocalConnectionAdapterTest extends ConnectionAdapterTestCase
{
    /**
     * {inheritdoc}
     */
    protected function createConnectionAdapter()
    {
        return new LocalConnectionAdapter();
    }
}
