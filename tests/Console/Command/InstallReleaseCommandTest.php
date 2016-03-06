<?php

namespace Accompli\Test;

use Accompli\Console\Command\InstallReleaseCommand;
use Nijens\ProtocolStream\StreamManager;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * InstallReleaseCommandTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class InstallReleaseCommandTest extends PHPUnit_Framework_TestCase
{
    /**
     * Unregisters the accompli stream wrapper.
     */
    public function tearDown()
    {
        StreamManager::create()->unregisterStream('accompli');
    }

    /**
     * Tests if InstallReleaseCommand::execute returns the success exit code (0) on succesful install.
     */
    public function testExecuteReturnsSuccessExitCode()
    {
        $application = new Application();
        $application->add(new InstallReleaseCommand());

        $command = $application->find('install-release');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
                'command' => $command->getName(),
                'version' => '0.1.0',
                '--project-dir' => __DIR__.'/../../Resources/CommandTesting/Success',
            )
        );

        $this->assertSame(0, $commandTester->getStatusCode());
    }

    /**
     * Tests if InstallReleaseCommand::execute returns the success exit code (1) on failed install.
     *
     * @depends testExecuteReturnsSuccessExitCode
     */
    public function testExecuteReturnsErrorExitCode()
    {
        $application = new Application();
        $application->add(new InstallReleaseCommand());

        $command = $application->find('install-release');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
                'command' => $command->getName(),
                'version' => '0.1.0',
                '--project-dir' => __DIR__.'/../../Resources/CommandTesting/Failure',
            )
        );

        $this->assertSame(1, $commandTester->getStatusCode());
    }
}
