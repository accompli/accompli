<?php

namespace Accompli\Console\Command;

use Accompli\Accompli;
use Accompli\Deployment\Host;
use Accompli\Task\TaskInterface;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Filesystem\Filesystem;

/**
 * InitCommand.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class InitCommand extends Command
{
    /**
     * The instance handling input and output of this command.
     *
     * @var SymfonyStyle
     */
    private $io;

    /**
     * The configuration of an accompli.json file.
     *
     * @var array
     */
    private $configuration = array(
        '$extend' => 'accompli://recipe/defaults.json',
        'hosts' => array(),
        'events' => array(
            'subscribers' => array(),
        ),
    );

    /**
     * Configures this command.
     */
    protected function configure()
    {
        $this
            ->setName('init')
            ->setDescription('Creates a basic accompli.json file in current directory.')
            ->addOption('working-dir', null, InputOption::VALUE_OPTIONAL, 'If specified, use the given directory as working directory.', getcwd());
    }

    /**
     * Interacts with the user to retrieve the configuration for an accompli.json file.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getIO($input, $output);
        $io->write(Accompli::LOGO);
        $io->writeln(' =========================');
        $io->writeln('  Configuration generator');
        $io->writeln(' =========================');

        $accompli = new Accompli(new ParameterBag());
        $accompli->initializeStreamWrapper();

        $recipe = $io->choice('Select a recipe to extend from', $this->getAvailableRecipes(), 'defaults.json');
        if ($recipe === 'Other') {
            $this->configuration['$extend'] = $io->ask('Enter the path to the recipe');
        } elseif ($recipe === 'None') {
            unset($this->configuration['$extend']);
        } else {
            $this->configuration['$extend'] = 'accompli://recipe/'.$recipe;
        }

        $this->interactForHostConfigurations($io);
        $this->interactForTaskConfigurations($io);

        $io->writeln(' The generator will create the following configuration:');
        $io->writeln($this->getJsonEncodedConfiguration());
        $io->confirm('Do you wish to continue?');
    }

    /**
     * Executes this command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getIO($input, $output);

        $configurationFile = $input->getOption('working-dir').'/accompli.json';

        $filesystem = new Filesystem();
        if ($filesystem->exists($configurationFile) === false || $io->confirm('An Accompli configuration file already exists. Do you wish to overwrite it?')) {
            $filesystem->dumpFile($configurationFile, $this->getJsonEncodedConfiguration());
        }
    }

    /**
     * Interacts with the user to retrieve host configurations.
     *
     * @param OutputStyle $io
     */
    private function interactForHostConfigurations(OutputStyle $io)
    {
        $io->writeln(' Add host configurations:');

        $addHost = true;
        while ($addHost) {
            $stage = $io->choice('Stage', array(
                Host::STAGE_PRODUCTION,
                Host::STAGE_ACCEPTANCE,
                Host::STAGE_TEST,
            ));
            $hostname = $io->ask('Hostname');
            $path = $io->ask('Workspace path');
            $connectionType = $io->ask('Connection type (eg. local or ssh)');

            $this->configuration['hosts'][] = array(
                'stage' => $stage,
                'connectionType' => $connectionType,
                'hostname' => $hostname,
                'path' => $path,
            );

            $addHost = $io->confirm('Add another host?');
        }
    }

    /**
     * Interacts with the user to retrieve task configurations.
     *
     * @param OutputStyle $io
     */
    private function interactForTaskConfigurations(OutputStyle $io)
    {
        $io->writeln(' Add tasks:');

        $addTask = true;
        while ($addTask) {
            $taskQuestion = new Question('Search for a task');
            $taskQuestion->setAutocompleterValues($this->getAvailableTasks());
            $taskQuestion->setValidator(function ($answer) {
                if (class_exists($answer) === false && class_exists('Accompli\\Task\\'.$answer) === true) {
                    $answer = 'Accompli\\Task\\'.$answer;
                }
                if (class_exists($answer) === false || in_array(TaskInterface::class, class_implements($answer)) === false) {
                    throw new InvalidArgumentException(sprintf('The task "%s" does not exist.', $answer));
                }

                return $answer;
            });

            $task = $io->askQuestion($taskQuestion);

            $taskConfiguration = array_merge(
                array(
                    'class' => $task,
                ),
                $this->getTaskConfigurationParameters($task)
            );

            $this->configuration['events']['subscribers'][] = $taskConfiguration;

            $addTask = $io->confirm('Add another task?');
        }
    }

    /**
     * Returns the required configuration parameters of the task.
     *
     * @param string $taskClass
     *
     * @return array
     */
    private function getTaskConfigurationParameters($taskClass)
    {
        $parameters = array();

        $reflectionClass = new ReflectionClass($taskClass);
        $reflectionConstructorMethod = $reflectionClass->getConstructor();
        if ($reflectionConstructorMethod instanceof ReflectionMethod) {
            foreach ($reflectionConstructorMethod->getParameters() as $reflectionParameter) {
                if ($reflectionParameter instanceof ReflectionParameter && $reflectionParameter->isDefaultValueAvailable() === false) {
                    $parameterValue = '';
                    if ($reflectionParameter->isArray()) {
                        $parameterValue = array();
                    }

                    $parameters[$reflectionParameter->getName()] = $parameterValue;
                }
            }
        }

        return $parameters;
    }

    /**
     * Returns the configuration as JSON string.
     *
     * @return string
     */
    private function getJsonEncodedConfiguration()
    {
        return json_encode($this->configuration, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n";
    }

    /**
     * Returns the instance handling input and output of this command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return OutputStyle
     */
    private function getIO(InputInterface $input, OutputInterface $output)
    {
        if ($this->io instanceof OutputStyle === false) {
            $this->io = new SymfonyStyle($input, $output);
        }

        return $this->io;
    }

    /**
     * Returns the available recipes within Accompli.
     *
     * @return array
     */
    private function getAvailableRecipes()
    {
        $recipes = array_diff(scandir('accompli://recipe/'), array('.', '..'));
        sort($recipes);
        $recipes[] = 'Other';
        $recipes[] = 'None';

        return $recipes;
    }

    /**
     * Returns the available tasks within Accompli.
     *
     * @return array
     */
    private function getAvailableTasks()
    {
        $tasks = array_map(function ($task) {
            return substr($task, 0, -4);
        }, array_diff(scandir(__DIR__.'/../../Task'), array('.', '..')));

        sort($tasks);

        return $tasks;
    }
}
