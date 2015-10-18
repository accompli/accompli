<?php

namespace Accompli\Test;

use Accompli\Deployment\Connection\SSHConnectionAdapter;
use Accompli\Test\Deployment\Connection\ConnectedConnectionAdapterTestCase;
use UnexpectedValueException;

/**
 * SSHConnectionAdapterTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class SSHConnectionAdapterTest extends ConnectedConnectionAdapterTestCase
{
    /**
     * Tests if constructing a new SSHConnectionAdapter instance throws an UnexpectedValueException when the authentication type is not known.
     *
     * @expectedException        UnexpectedValueException
     * @expectedExceptionMessage Invalid SSH authentication type: unknown
     */
    public function testConstructWithUnknownAuthenticationTypeThrowsUnexpectedValueException()
    {
        new SSHConnectionAdapter('localhost', 'unknown');
    }

    /**
     * Tests if constructing a new SSHConnectionAdapter instance with username and password succeeds.
     */
    public function testConstructWithPasswordAuthenticationType()
    {
        $this->connectionAdapter = new SSHConnectionAdapter('localhost', SSHConnectionAdapter::AUTHENTICATION_PASSWORD, $this->getSSHUsername(), getenv('ssh.password'));
    }

    /**
     * Tests if SSHConnectionAdapter::connect returns true with password authentication type.
     */
    public function testConnectWithAuthenticationTypePasswordReturnsTrue()
    {
        $this->connectionAdapter = new SSHConnectionAdapter('localhost', SSHConnectionAdapter::AUTHENTICATION_PASSWORD, $this->getSSHUsername(), getenv('ssh.password'));

        if (get_current_user() === 'travis') {
            $this->markTestSkipped('This test is not properly set up to be tested with Travis CI.');
        }

        $this->assertTrue($this->connectionAdapter->connect());
    }

    /**
     * Tests if SSHConnectionAdapter::connect returns true with SSH agent authentication type.
     */
    public function testConnectWithAuthenticationTypeSSHAgentReturnsTrue()
    {
        $this->connectionAdapter = new SSHConnectionAdapter('localhost', SSHConnectionAdapter::AUTHENTICATION_SSH_AGENT, $this->getSSHUsername());

        $this->assertTrue($this->connectionAdapter->connect());
    }

    /**
     * {@inheritdoc}
     */
    protected function createConnectionAdapter()
    {
        return new SSHConnectionAdapter('localhost', SSHConnectionAdapter::AUTHENTICATION_PUBLIC_KEY, $this->getSSHUsername());
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
