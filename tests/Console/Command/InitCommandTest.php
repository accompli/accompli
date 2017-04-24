<?php

namespace Accompli\Test\Console\Command;

use Accompli\Console\Command\InitCommand;
use Nijens\ProtocolStream\StreamManager;
use PHPUnit_Framework_TestCase;
use ReflectionProperty;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\SymfonyQuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

/**
 * InitCommandTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class InitCommandTest extends PHPUnit_Framework_TestCase
{
    /**
     * Removes the created accompli.json file.
     */
    public function tearDown()
    {
        StreamManager::create()->unregisterStream('accompli');

        $filesystem = new Filesystem();
        $filesystem->remove(__DIR__.'/accompli.json');
    }

    /**
     * Tests if executing the InitCommand non-interactively creates an 'empty' accompli.json file.
     */
    public function testExecuteNonInteractive()
    {
        $application = new Application();
        $application->add(new InitCommand());

        $command = $application->find('init');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--working-dir' => __DIR__,
            ),
            array(
                'interactive' => false,
            )
        );

        $this->assertFileExists(__DIR__.'/accompli.json');
        $this->assertJsonFileEqualsJsonFile(__DIR__.'/../../Resources/InitCommand/non-interactive-accompli.json', __DIR__.'/accompli.json');
    }

    /**
     * Tests if executing the InitCommand interactively creates an accompli.json file with the expected configuration.
     */
    public function testExecuteInteractive()
    {
        $application = new Application();
        $application->add(new InitCommand());

        $command = $application->find('init');

        $commandTester = new CommandTester($command);
        $commandTester->setInputStream("\n0\nexample.com\n/var/www/example.com\nssh\nn\nExecuteCommandTask\nn\n\n");

        $input = new ArrayInput(
            array(
                'command' => $command->getName(),
                '--working-dir' => __DIR__,
            )
        );
        $output = new StreamOutput(fopen('php://memory', 'w', false));

        $io = new SymfonyStyle($input, $output);

        $questionHelper = new SymfonyQuestionHelper();
        $questionHelper->setInputStream($commandTester->getInputStream());

        $questionHelperReflectionProperty = new ReflectionProperty(SymfonyStyle::class, 'questionHelper');
        $questionHelperReflectionProperty->setAccessible(true);
        $questionHelperReflectionProperty->setValue($io, $questionHelper);

        $commandTester->injectIntoCommandProperty('io', $io);

        $commandTester->execute($input, array(), $output);

        $this->assertFileExists(__DIR__.'/accompli.json');
        $this->assertJsonFileEqualsJsonFile(__DIR__.'/../../Resources/InitCommand/interactive-accompli.json', __DIR__.'/accompli.json');
    }

    /**
     * Tests if executing the InitCommand interactively asks to overwrite an existing accompli.json configuration.
     *
     * @depends testExecuteInteractive
     */
    public function testExecuteInteractiveConfirmOverwritingExistingConfiguration()
    {
        $application = new Application();
        $application->add(new InitCommand());

        $command = $application->find('init');

        $commandTester = new CommandTester($command);
        $commandTester->setInputStream("\n0\nexample.com\n/var/www/example.com\nssh\nn\nNonExistingTask\nExecuteCommandTask\nn\n\nn\n");

        $input = new ArrayInput(
            array(
                'command' => $command->getName(),
                '--no-ansi',
                '--working-dir' => __DIR__,
            )
        );
        $output = new StreamOutput(fopen('php://memory', 'w', false));

        $io = new SymfonyStyle($input, $output);

        $questionHelper = new SymfonyQuestionHelper();
        $questionHelper->setInputStream($commandTester->getInputStream());

        $questionHelperReflectionProperty = new ReflectionProperty(SymfonyStyle::class, 'questionHelper');
        $questionHelperReflectionProperty->setAccessible(true);
        $questionHelperReflectionProperty->setValue($io, $questionHelper);

        $commandTester->injectIntoCommandProperty('io', $io);

        touch(__DIR__.'/accompli.json');

        $commandTester->execute($input, array(), $output);

        $this->assertRegExp('/An Accompli configuration file already exists. Do you wish to overwrite it?/', $commandTester->getDisplay());
        $this->assertEquals('', file_get_contents(__DIR__.'/accompli.json'));
    }

    /**
     * Tests if executing the InitCommand interactively throws an InvalidArgumentException when attempting to add a non-existing task without affecting the expected accompli.json configuration.
     *
     * @depends testExecuteInteractive
     */
    public function testExecuteInteractiveThrowsInvalidArgumentExceptionForInvalidOrNonExistingTask()
    {
        $application = new Application();
        $application->add(new InitCommand());

        $command = $application->find('init');

        $commandTester = new CommandTester($command);
        $commandTester->setInputStream("\n0\nexample.com\n/var/www/example.com\nssh\nn\nNonExistingTask\nExecuteCommandTask\nn\n\n");

        $input = new ArrayInput(
            array(
                'command' => $command->getName(),
                '--working-dir' => __DIR__,
            )
        );
        $output = new StreamOutput(fopen('php://memory', 'w', false));

        $io = new SymfonyStyle($input, $output);

        $questionHelper = new SymfonyQuestionHelper();
        $questionHelper->setInputStream($commandTester->getInputStream());

        $questionHelperReflectionProperty = new ReflectionProperty(SymfonyStyle::class, 'questionHelper');
        $questionHelperReflectionProperty->setAccessible(true);
        $questionHelperReflectionProperty->setValue($io, $questionHelper);

        $commandTester->injectIntoCommandProperty('io', $io);

        $commandTester->execute($input, array(), $output);

        $this->assertRegExp('/The task "NonExistingTask" does not exist./', $commandTester->getDisplay());
        $this->assertFileExists(__DIR__.'/accompli.json');
        $this->assertJsonFileEqualsJsonFile(__DIR__.'/../../Resources/InitCommand/interactive-accompli.json', __DIR__.'/accompli.json');
    }

    /**
     * Tests if executing the InitCommand interactively creates an accompli.json file with the expected configuration with alternate recipe.
     *
     * @depends testExecuteInteractive
     */
    public function testExecuteInteractiveAlternateRecipe()
    {
        $application = new Application();
        $application->add(new InitCommand());

        $command = $application->find('init');

        $commandTester = new CommandTester($command);
        $commandTester->setInputStream("1\n/path/to/alternate/recipe.json\n0\nexample.com\n/var/www/example.com\nssh\nn\nExecuteCommandTask\nn\n\n");

        $input = new ArrayInput(
            array(
                'command' => $command->getName(),
                '--working-dir' => __DIR__,
            )
        );
        $output = new StreamOutput(fopen('php://memory', 'w', false));

        $io = new SymfonyStyle($input, $output);

        $questionHelper = new SymfonyQuestionHelper();
        $questionHelper->setInputStream($commandTester->getInputStream());

        $questionHelperReflectionProperty = new ReflectionProperty(SymfonyStyle::class, 'questionHelper');
        $questionHelperReflectionProperty->setAccessible(true);
        $questionHelperReflectionProperty->setValue($io, $questionHelper);

        $commandTester->injectIntoCommandProperty('io', $io);

        $commandTester->execute($input, array(), $output);

        $this->assertFileExists(__DIR__.'/accompli.json');
        $this->assertJsonFileEqualsJsonFile(__DIR__.'/../../Resources/InitCommand/interactive-alternate-recipe-accompli.json', __DIR__.'/accompli.json');
    }

    /**
     * Tests if executing the InitCommand interactively creates an accompli.json file with the expected configuration without recipe.
     *
     * @depends testExecuteInteractive
     */
    public function testExecuteInteractiveNoRecipe()
    {
        $application = new Application();
        $application->add(new InitCommand());

        $command = $application->find('init');

        $commandTester = new CommandTester($command);
        $commandTester->setInputStream("0\n0\nexample.com\n/var/www/example.com\nssh\nn\nExecuteCommandTask\nn\n\n\n");

        $input = new ArrayInput(
            array(
                'command' => $command->getName(),
                '--working-dir' => __DIR__,
            )
        );
        $output = new StreamOutput(fopen('php://memory', 'w', false));

        $io = new SymfonyStyle($input, $output);

        $questionHelper = new SymfonyQuestionHelper();
        $questionHelper->setInputStream($commandTester->getInputStream());

        $questionHelperReflectionProperty = new ReflectionProperty(SymfonyStyle::class, 'questionHelper');
        $questionHelperReflectionProperty->setAccessible(true);
        $questionHelperReflectionProperty->setValue($io, $questionHelper);

        $commandTester->injectIntoCommandProperty('io', $io);

        $commandTester->execute($input, array(), $output);

        $this->assertFileExists(__DIR__.'/accompli.json');
        $this->assertJsonFileEqualsJsonFile(__DIR__.'/../../Resources/InitCommand/interactive-no-recipe-accompli.json', __DIR__.'/accompli.json');
    }
}
