<?php

namespace Accompli\Test\Report;

use Accompli\DataCollector\EventDataCollector;
use PHPUnit_Framework_TestCase;
use Psr\Log\LogLevel;

/**
 * AbstractReportTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class AbstractReportTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if constructing a new AbstractReport sets the instance properties.
     */
    public function testConstruct()
    {
        $eventDataCollectorMock = $this->getMockBuilder('Accompli\DataCollector\EventDataCollector')
                ->getMock();

        $report = $this->getMockBuilder('Accompli\Report\AbstractReport')
                ->setConstructorArgs(array($eventDataCollectorMock, array($eventDataCollectorMock)))
                ->getMockForAbstractClass();

        $this->assertAttributeSame($eventDataCollectorMock, 'eventDataCollector', $report);
        $this->assertAttributeSame(array($eventDataCollectorMock), 'dataCollectors', $report);
    }

    /**
     * Tests if AbstractReport::generate generates the expected output.
     *
     * @dataProvider provideTestGenerate
     *
     * @param EventDataCollector $eventDataCollectorMock
     * @param string             $beforeAfterTitleBlockLine
     * @param string             $titleBlockLine
     */
    public function testGenerate(EventDataCollector $eventDataCollectorMock, $beforeAfterTitleBlockLine, $titleBlockLine)
    {
        $dataCollectorMock = $this->getMockBuilder('Accompli\DataCollector\DataCollectorInterface')
                ->getMock();
        $dataCollectorMock->expects($this->once())
                ->method('getData')
                ->willReturn(array('Test item' => 'Test item data'));

        $outputFormatterMock = $this->getMockBuilder('Symfony\Component\Console\Formatter\OutputFormatterInterface')
                ->getMock();
        $outputFormatterMock->expects($this->any())
                ->method('format')
                ->willReturnArgument(0);

        $outputMock = $this->getMockBuilder('Symfony\Component\Console\Output\OutputInterface')
                ->getMock();
        $outputMock->expects($this->any())
                ->method('getFormatter')
                ->willReturn($outputFormatterMock);
        $outputMock->expects($this->exactly(9))
                ->method('writeln')
                ->withConsecutive(
                    array($this->equalTo('')),
                    array($this->stringStartsWith($beforeAfterTitleBlockLine)),
                    array($this->stringStartsWith($titleBlockLine)),
                    array($this->stringStartsWith($beforeAfterTitleBlockLine)),
                    array($this->equalTo('')),
                    array($this->equalTo(' ----------- ---------------- ')),
                    array($this->equalTo('')),
                    array($this->equalTo(' ----------- ---------------- ')),
                    array($this->equalTo(''))
                );
        $outputMock->expects($this->exactly(5))
                ->method('write')
                ->withConsecutive(
                    array($this->equalTo(' ')),
                    array($this->equalTo(' Test item ')),
                    array($this->equalTo(' ')),
                    array($this->equalTo(' Test item data ')),
                    array($this->equalTo(' '))
                );

        $loggerMock = $this->getMockBuilder('Accompli\Console\Logger\ConsoleLoggerInterface')
                ->getMock();
        $loggerMock->expects($this->once())
                ->method('getOutput')
                ->willReturn($outputMock);

        $report = $this->getMockBuilder('Accompli\Report\AbstractReport')
                ->setConstructorArgs(array($eventDataCollectorMock, array($eventDataCollectorMock, $dataCollectorMock)))
                ->getMockForAbstractClass();

        $report->generate($loggerMock);
    }

    /**
     * Returns an array with the various (test) scenarios for AbstractReport::generate.
     *
     * @return array
     */
    public function provideTestGenerate()
    {
        $provide = array();

        $eventDataCollectorMock = $this->getMockBuilder('Accompli\DataCollector\EventDataCollector')
                ->getMock();
        $eventDataCollectorMock->expects($this->once())
                ->method('getData')
                ->willReturn(array());

        $provide[] = array($eventDataCollectorMock, '<fg=black;bg=green>', '<fg=black;bg=green> [OK]');

        $eventDataCollectorMock = $this->getMockBuilder('Accompli\DataCollector\EventDataCollector')
                ->getMock();
        $eventDataCollectorMock->expects($this->once())
                ->method('hasCountedFailedEvents')
                ->willReturn(true);
        $eventDataCollectorMock->expects($this->once())
                ->method('getData')
                ->willReturn(array());

        $provide[] = array($eventDataCollectorMock, '<fg=white;bg=red>', '<fg=white;bg=red> [FAILURE]');

        $eventDataCollectorMock = $this->getMockBuilder('Accompli\DataCollector\EventDataCollector')
                ->getMock();
        $eventDataCollectorMock->expects($this->once())
                ->method('hasCountedLogLevel')
                ->with(LogLevel::EMERGENCY)
                ->willReturn(true);
        $eventDataCollectorMock->expects($this->once())
                ->method('getData')
                ->willReturn(array());

        $provide[] = array($eventDataCollectorMock, '<fg=black;bg=yellow>', '<fg=black;bg=yellow> [OK, but with errors]');

        return $provide;
    }
}
