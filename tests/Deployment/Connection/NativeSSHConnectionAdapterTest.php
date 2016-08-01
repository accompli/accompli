<?php

namespace Accompli\Test\Deployment\Connection;

use Accompli\Deployment\Connection\NativeSSHConnectionAdapter;

/**
 * NativeSSHConnectionAdapterTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class NativeSSHConnectionAdapterTest extends ConnectedConnectionAdapterTestCase
{
    /**
     * Unsets the USERPROFILE environment variable and sets the HOME environment variable.
     */
    public function tearDown()
    {
        parent::tearDown();

        if (isset($_SERVER['USERPROFILE'])) {
            $_SERVER['HOME'] = $_SERVER['USERPROFILE'];
            unset($_SERVER['USERPROFILE']);
        }
    }

    /**
     * Tests if NativeSSHConnectionAdapter::connect returns true, but does not recreate the connection.
     */
    public function testCallingConnectTwiceDoesNotRecreateConnection()
    {
        $this->connectionAdapter->connect();

        $connection = $this->getObjectAttribute($this->connectionAdapter, 'process');

        $this->assertTrue($this->connectionAdapter->connect());
        $this->assertAttributeSame($connection, 'process', $this->connectionAdapter);
    }

    /**
     * Tests if NativeSSHConnectionAdapter::connect returns true when connecting with an existing SSH configuration file.
     */
    public function testConnectWithSSHConfigurationReturnsTrue()
    {
        $username = $this->getSSHUsername();
        if (isset($username) === false) {
            $username = $_SERVER['USER'];
        }

        touch('/home/'.$username.'/.ssh/config');

        $this->testConnectReturnsTrue();
    }

    /**
     * {@inheritdoc}
     */
    protected function createConnectionAdapter()
    {
        return new NativeSSHConnectionAdapter('localhost', $this->getSSHUsername());
    }

    /**
     * Returns the SSH username configured in the PHPUnit configuration.
     *
     * @return string|null
     */
    private function getSSHUsername()
    {
        $username = getenv('ssh.username');
        if (empty($username)) {
            $username = null;
        }

        return $username;
    }
}
