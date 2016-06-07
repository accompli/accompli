<?php

namespace Accompli\Test\Console\Helper;

use Accompli\Console\Helper\Title;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * TitleTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class TitleTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if constructing a new Title instance sets the properties.
     */
    public function testConstruct()
    {
        $outputMock = $this->getMockBuilder(OutputInterface::class)
                ->getMock();

        $title = new Title($outputMock, 'Test message');

        $this->assertAttributeSame($outputMock, 'output', $title);
        $this->assertAttributeSame('Test message', 'message', $title);
    }

    /**
     * Tests if TitleBlock::render writes the lines to the output instance to render a title.
     */
    public function testRender()
    {
        $outputFormatter = new OutputFormatter();

        $outputMock = $this->getMockBuilder(OutputInterface::class)
                ->getMock();
        $outputMock->expects($this->once())
                ->method('getFormatter')
                ->willReturn($outputFormatter);
        $outputMock->expects($this->once())
                ->method('writeln')
                ->with(
                    $this->equalTo(
                        array(
                            '',
                            '<title>Test message</>',
                            '<title>============</>',
                            '',
                        )
                    )
                );

        $title = new Title($outputMock, 'Test message');
        $title->render();
    }
}
