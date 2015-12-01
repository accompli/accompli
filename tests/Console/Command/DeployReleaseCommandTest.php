<?php

namespace Accompli\Test;

use Accompli\Console\Command\DeployReleaseCommand;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * DeployReleaseCommandTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class DeployReleaseCommandTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if DeployReleaseCommand::execute returns the success exit code (0) on succesful deployment.
     */
    public function testExecuteReturnsSuccessExitCode()
    {
        $application = new Application();
        $application->add(new DeployReleaseCommand());

        $command = $application->find('deploy-release');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
                'command' => $command->getName(),
                'version' => '0.1.0',
                'stage' => 'test',
                '--project-dir' => __DIR__.'/../../Resources/CommandTesting/Success',
            )
        );

        $this->assertSame(0, $commandTester->getStatusCode());
    }

    /**
     * Tests if DeployReleaseCommand::execute returns the success exit code (1) on failed deployment.
     *
     * @depends testExecuteReturnsSuccessExitCode
     */
    public function testExecuteReturnsErrorExitCode()
    {
        $application = new Application();
        $application->add(new DeployReleaseCommand());

        $command = $application->find('deploy-release');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
                'command' => $command->getName(),
                'version' => '0.1.0',
                'stage' => 'test',
                '--project-dir' => __DIR__.'/../../Resources/CommandTesting/Failure',
            )
        );

        $this->assertSame(1, $commandTester->getStatusCode());
    }
}
