<?php

namespace Accompli\Test;

use Accompli\Console\Logger\ConsoleLogger;
use Accompli\Task\TaskInterface;
use PHPUnit_Framework_TestCase;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ConsoleLoggerTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class ConsoleLoggerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if constructing a new ConsoleLogger sets the properties.
     */
    public function testConstruct()
    {
        $outputMock = $this->getMockBuilder('Symfony\Component\Console\Output\OutputInterface')->getMock();

        $logger = new ConsoleLogger($outputMock);

        $this->assertAttributeSame($outputMock, 'output', $logger);
    }

    /**
     * Tests if constructing a new ConsoleLogger on a Windows operating system changes the task action status map property.
     */
    public function testConstructUpdatesTaskActionStatusMapBasedOnOperatingSystem()
    {
        $outputMock = $this->getMockBuilder('Symfony\Component\Console\Output\OutputInterface')->getMock();

        $logger = $this->getMockBuilder('Accompli\Console\Logger\ConsoleLogger')
                ->disableOriginalConstructor()
                ->setMethods(array('getOperatingSystemName'))
                ->getMock();
        $logger->expects($this->once())->method('getOperatingSystemName')->willReturn('WIN');
        $logger->__construct($outputMock);

        $expectedValue = array(
            TaskInterface::ACTION_IN_PROGRESS => '...',
            TaskInterface::ACTION_COMPLETED => ' '.chr(251).' ',
            TaskInterface::ACTION_FAILED => '!!!',
        );

        $this->assertAttributeSame($expectedValue, 'taskActionStatusToOutputMap', $logger);
    }

    /**
     * Tests if ConsoleLogger::log with an invalid LogLevel throws an InvalidArgumentException.
     *
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage The log level "invalid" does not exist.
     */
    public function testLogWithInvalidLogLevelThrowsInvalidArgumentException()
    {
        $outputMock = $this->getMockBuilder('Symfony\Component\Console\Output\OutputInterface')->getMock();

        $logger = new ConsoleLogger($outputMock);
        $logger->log('invalid', 'message');
    }

    /**
     * Tests if ConsoleLogger::log writes the log to the output based on LogLevel and verbosity level.
     *
     * @dataProvider provideTestLogWritesLogLevelsToOutputBasedOnVerbosity
     *
     * @param string $logLevel
     * @param string $verbosityLevel
     * @param bool   $output
     */
    public function testLogWritesLogLevelsToOutputBasedOnVerbosity($logLevel, $verbosityLevel, $output)
    {
        $outputFormatterMock = $this->getMockBuilder('Symfony\Component\Console\Formatter\OutputFormatterInterface')->getMock();

        $outputMock = $this->getMockBuilder('Symfony\Component\Console\Output\OutputInterface')->getMock();
        $outputMock->expects($this->once())->method('getVerbosity')->willReturn($verbosityLevel);
        $outputMock->expects($this->any())->method('getFormatter')->willReturn($outputFormatterMock);

        $callOccurence = $this->never();
        if ($output === true) {
            $callOccurence = $this->once();
        }

        $outputMock->expects($callOccurence)->method('writeln');

        $logger = new ConsoleLogger($outputMock);
        $logger->log($logLevel, 'message');
    }

    /**
     * Tests if ConsoleLogger::log writes certain LogLevels to the error output (when available).
     *
     * @dataProvider provideTestLogWritesLogLevelsToErrorOutput
     *
     * @param string $logLevel
     */
    public function testLogWritesLogLevelsToErrorOutput($logLevel)
    {
        $outputFormatterMock = $this->getMockBuilder('Symfony\Component\Console\Formatter\OutputFormatterInterface')->getMock();

        $errorOutputMock = $this->getMockBuilder('Symfony\Component\Console\Output\OutputInterface')->getMock();
        $errorOutputMock->expects($this->once())->method('getVerbosity')->willReturn(OutputInterface::VERBOSITY_NORMAL);
        $errorOutputMock->expects($this->once())->method('writeln');

        $outputMock = $this->getMockBuilder('Symfony\Component\Console\Output\ConsoleOutputInterface')->getMock();
        $outputMock->expects($this->once())->method('getErrorOutput')->willReturn($errorOutputMock);
        $outputMock->expects($this->never())->method('getVerbosity');
        $outputMock->expects($this->any())->method('getFormatter')->willReturn($outputFormatterMock);
        $outputMock->expects($this->never())->method('writeln');

        $logger = new ConsoleLogger($outputMock);
        $logger->log($logLevel, 'message');
    }

    /**
     * Tests if ConsoleLogger::log writes PSR-3 context replacements to the output.
     */
    public function testLogContextReplacements()
    {
        $outputFormatterMock = $this->getMockBuilder('Symfony\Component\Console\Formatter\OutputFormatterInterface')->getMock();

        $outputMock = $this->getMockBuilder('Symfony\Component\Console\Output\OutputInterface')->getMock();
        $outputMock->expects($this->once())->method('getVerbosity')->willReturn(OutputInterface::VERBOSITY_NORMAL);
        $outputMock->expects($this->any())->method('getFormatter')->willReturn($outputFormatterMock);
        $outputMock->expects($this->once())->method('writeln')->with($this->equalTo('  <info>Message to Bob</info>'));

        $logger = new ConsoleLogger($outputMock);
        $logger->log(LogLevel::INFO, 'Message to {user}', array('user' => 'Bob'));
    }

    /**
     * Tests if ConsoleLogger::log creates message sections based on the context.
     */
    public function testLogMessageSections()
    {
        $outputFormatterMock = $this->getMockBuilder('Symfony\Component\Console\Formatter\OutputFormatterInterface')->getMock();

        $outputMock = $this->getMockBuilder('Symfony\Component\Console\Output\OutputInterface')->getMock();
        $outputMock->expects($this->once())->method('getVerbosity')->willReturn(OutputInterface::VERBOSITY_NORMAL);
        $outputMock->expects($this->any())->method('getFormatter')->willReturn($outputFormatterMock);
        $outputMock->expects($this->once())
                ->method('writeln')
                ->with($this->equalTo('[<event-name>accompli.test                     </event-name>][<event-task-name>TestTask                 </event-task-name>]  <info>message</info>'));

        $logger = new ConsoleLogger($outputMock);
        $logger->log(LogLevel::INFO, 'message', array('event.name' => 'accompli.test', 'event.task.name' => 'TestTask'));
    }

    /**
     * Tests if ConsoleLogger::log does not repeat the same message sections, but indents the message.
     */
    public function testLogMessageSectionsWithSameContextIndented()
    {
        $outputFormatterMock = $this->getMockBuilder('Symfony\Component\Console\Formatter\OutputFormatterInterface')->getMock();

        $outputMock = $this->getMockBuilder('Symfony\Component\Console\Output\OutputInterface')->getMock();
        $outputMock->expects($this->exactly(2))->method('getVerbosity')->willReturn(OutputInterface::VERBOSITY_NORMAL);
        $outputMock->expects($this->any())->method('getFormatter')->willReturn($outputFormatterMock);
        $outputMock->expects($this->exactly(2))
                ->method('writeln')
                ->withConsecutive(
                    array($this->equalTo('[<event-name>accompli.test                     </event-name>][<event-task-name>TestTask                 </event-task-name>]  <info>message</info>')),
                    array($this->equalTo('                                                                 <info>message</info>'))
                );

        $logger = new ConsoleLogger($outputMock);
        $logger->log(LogLevel::INFO, 'message', array('event.name' => 'accompli.test', 'event.task.name' => 'TestTask'));
        $logger->log(LogLevel::INFO, 'message', array('event.name' => 'accompli.test', 'event.task.name' => 'TestTask'));
    }

    /**
     * Tests if ConsoleLogger::log adds a task status section to the message based on the context.
     *
     * @dataProvider provideTestLogTaskActionStatus
     *
     * @param string $actionStatus
     * @param string $expectedLogMessage
     */
    public function testLogTaskActionStatus($actionStatus, $expectedLogMessage)
    {
        $outputFormatterMock = $this->getMockBuilder('Symfony\Component\Console\Formatter\OutputFormatterInterface')->getMock();

        $outputMock = $this->getMockBuilder('Symfony\Component\Console\Output\OutputInterface')->getMock();
        $outputMock->expects($this->once())->method('getVerbosity')->willReturn(OutputInterface::VERBOSITY_NORMAL);
        $outputMock->expects($this->any())->method('getFormatter')->willReturn($outputFormatterMock);
        $outputMock->expects($this->once())
                ->method('writeln')
                ->with($this->equalTo($expectedLogMessage));

        $logger = new ConsoleLogger($outputMock);
        $logger->log(LogLevel::INFO, 'message', array('event.name' => 'accompli.test', 'event.task.name' => 'TestTask', 'event.task.action' => $actionStatus));
    }

    /**
     * Tests if ConsoleLogger::log replaces a previous line when output.resetLine is added to the context.
     */
    public function testLogReplaceLine()
    {
        $outputMock = $this->getMockBuilder('Symfony\Component\Console\Output\OutputInterface')->getMock();
        $outputMock->expects($this->exactly(2))->method('getVerbosity')->willReturn(OutputInterface::VERBOSITY_NORMAL);
        $outputMock->expects($this->exactly(3))->method('isDecorated')->willReturn(true);
        $outputMock->expects($this->exactly(3))
                ->method('getFormatter')
                ->willReturn(new OutputFormatter(true));
        $outputMock->expects($this->once())
                ->method('write')
                ->with($this->equalTo("\033[1A\e[63C\e[0J"));
        $outputMock->expects($this->exactly(2))
                ->method('writeln')
                ->withConsecutive(
                    array($this->equalTo('[<event-name>accompli.test                     </event-name>][<event-task-name>TestTask                 </event-task-name>]  <info>message</info>')),
                    array($this->equalTo('  <info>message</info>'))
                );

        $logger = $this->getMockBuilder('Accompli\Console\Logger\ConsoleLogger')
                ->setConstructorArgs(array($outputMock))
                ->setMethods(array('getTerminalWidth'))
                ->getMock();
        $logger->expects($this->exactly(2))->method('getTerminalWidth')->willReturn(150);

        $logger->log(LogLevel::INFO, 'message', array('event.name' => 'accompli.test', 'event.task.name' => 'TestTask'));
        $logger->log(LogLevel::INFO, 'message', array('event.name' => 'accompli.test', 'event.task.name' => 'TestTask', 'output.resetLine' => true));
    }

    /**
     * Returns an array with test cases for @see testLogWritesLogLevelsToOutputBasedOnVerbosity.
     *
     * @return array
     */
    public function provideTestLogWritesLogLevelsToOutputBasedOnVerbosity()
    {
        return array(
            array(LogLevel::EMERGENCY, OutputInterface::VERBOSITY_QUIET, false),
            array(LogLevel::ALERT, OutputInterface::VERBOSITY_QUIET, false),
            array(LogLevel::CRITICAL, OutputInterface::VERBOSITY_QUIET, false),
            array(LogLevel::ERROR, OutputInterface::VERBOSITY_QUIET, false),
            array(LogLevel::WARNING, OutputInterface::VERBOSITY_QUIET, false),
            array(LogLevel::NOTICE, OutputInterface::VERBOSITY_QUIET, false),
            array(LogLevel::INFO, OutputInterface::VERBOSITY_QUIET, false),
            array(LogLevel::DEBUG, OutputInterface::VERBOSITY_QUIET, false),
            array(LogLevel::EMERGENCY, OutputInterface::VERBOSITY_NORMAL, true),
            array(LogLevel::ALERT, OutputInterface::VERBOSITY_NORMAL, true),
            array(LogLevel::CRITICAL, OutputInterface::VERBOSITY_NORMAL, true),
            array(LogLevel::ERROR, OutputInterface::VERBOSITY_NORMAL, true),
            array(LogLevel::WARNING, OutputInterface::VERBOSITY_NORMAL, true),
            array(LogLevel::NOTICE, OutputInterface::VERBOSITY_NORMAL, true),
            array(LogLevel::INFO, OutputInterface::VERBOSITY_NORMAL, true),
            array(LogLevel::DEBUG, OutputInterface::VERBOSITY_NORMAL, false),
            array(LogLevel::ALERT, OutputInterface::VERBOSITY_DEBUG, true),
            array(LogLevel::CRITICAL, OutputInterface::VERBOSITY_DEBUG, true),
            array(LogLevel::ERROR, OutputInterface::VERBOSITY_DEBUG, true),
            array(LogLevel::WARNING, OutputInterface::VERBOSITY_DEBUG, true),
            array(LogLevel::NOTICE, OutputInterface::VERBOSITY_DEBUG, true),
            array(LogLevel::INFO, OutputInterface::VERBOSITY_DEBUG, true),
            array(LogLevel::DEBUG, OutputInterface::VERBOSITY_DEBUG, true),
        );
    }

    /**
     * Returns an array with test cases for @see testLogWritesLogLevelsToErrorOutput.
     *
     * @return array
     */
    public function provideTestLogWritesLogLevelsToErrorOutput()
    {
        return array(
            array(LogLevel::EMERGENCY),
            array(LogLevel::ALERT),
            array(LogLevel::CRITICAL),
            array(LogLevel::ERROR),
        );
    }

    /**
     * Returns an array with test cases for @see testLogTaskActionStatus.
     *
     * @return array
     */
    public function provideTestLogTaskActionStatus()
    {
        return array(
            array(TaskInterface::ACTION_IN_PROGRESS, '[<event-name>accompli.test                     </event-name>][<event-task-name>TestTask                 </event-task-name>] [<event-task-action-in_progress>...</event-task-action-in_progress>] <info>message</info>'),
            array(TaskInterface::ACTION_COMPLETED, '[<event-name>accompli.test                     </event-name>][<event-task-name>TestTask                 </event-task-name>] [<event-task-action-completed> ✓ </event-task-action-completed>] <info>message</info>'),
            array(TaskInterface::ACTION_FAILED, '[<event-name>accompli.test                     </event-name>][<event-task-name>TestTask                 </event-task-name>] [<event-task-action-failed>!!!</event-task-action-failed>] <info>message</info>'),
        );
    }
}
