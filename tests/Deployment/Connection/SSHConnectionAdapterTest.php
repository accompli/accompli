<?php

namespace Accompli\Test\Deployment\Connection;

use Accompli\Deployment\Connection\SSHConnectionAdapter;
use UnexpectedValueException;

/**
 * SSHConnectionAdapterTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class SSHConnectionAdapterTest extends ConnectedConnectionAdapterTestCase
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
     * Tests if constructing a new SSHConnectionAdapter instance in a (emulated) Windows operating system environment loads the RSA key.
     */
    public function testConstructInWindowsOperatingSystemEnvironment()
    {
        $_SERVER['USERPROFILE'] = $_SERVER['HOME'];
        unset($_SERVER['HOME']);

        $this->connectionAdapter = $this->createConnectionAdapter();

        $this->assertAttributeInstanceOf('phpseclib\Crypt\RSA', 'authenticationCredentials', $this->connectionAdapter);
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
        if (isset($_SERVER['SSH_AUTH_SOCK']) === false) {
            $this->markTestSkipped('No running SSH agent found.');
        }

        $this->connectionAdapter = new SSHConnectionAdapter('localhost', SSHConnectionAdapter::AUTHENTICATION_SSH_AGENT, $this->getSSHUsername());

        $this->assertTrue($this->connectionAdapter->connect());
    }

    /**
     * Tests if SSHConnectionAdapter::connect returns true, but does not recreate the connection.
     */
    public function testCallingConnectTwiceDoesNotRecreateConnection()
    {
        $this->connectionAdapter->connect();

        $connection = $this->getObjectAttribute($this->connectionAdapter, 'connection');

        $this->assertTrue($this->connectionAdapter->connect());
        $this->assertAttributeSame($connection, 'connection', $this->connectionAdapter);
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
