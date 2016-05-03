<?php

namespace Accompli\Test\Console\Helper;

use Accompli\Console\Helper\TitleBlock;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Formatter\OutputFormatter;

/**
 * TitleBlockTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class TitleBlockTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if constructing a new TitleBlock instance sets the properties.
     */
    public function testConstruct()
    {
        $outputMock = $this->getMockBuilder('Symfony\Component\Console\Output\OutputInterface')
                ->getMock();

        $titleBlock = new TitleBlock($outputMock, 'Test message', TitleBlock::STYLE_SUCCESS);

        $this->assertAttributeSame($outputMock, 'output', $titleBlock);
        $this->assertAttributeSame('Test message', 'message', $titleBlock);
        $this->assertAttributeSame(TitleBlock::STYLE_SUCCESS, 'style', $titleBlock);
    }

    /**
     * Tests if TitleBlock::render writes the lines to the output instance to render a title block.
     *
     * @dataProvider provideTestRender
     *
     * @param string $style
     * @param string $beforeAfterLine
     * @param string $messageLine
     */
    public function testRender($style, $beforeAfterLine, $messageLine)
    {
        $outputFormatter = new OutputFormatter();

        $outputMock = $this->getMockBuilder('Symfony\Component\Console\Output\OutputInterface')
                ->getMock();
        $outputMock->expects($this->exactly(3))
                ->method('getFormatter')
                ->willReturn($outputFormatter);
        $outputMock->expects($this->exactly(5))
                ->method('writeln')
                ->withConsecutive(
                    array($this->equalTo('')),
                    array($this->stringStartsWith($beforeAfterLine)),
                    array($this->stringStartsWith($messageLine)),
                    array($this->stringStartsWith($beforeAfterLine)),
                    array($this->equalTo(''))
                );

        $title = new TitleBlock($outputMock, 'Test message', $style);
        $title->render();
    }

    /**
     * Returns an array with the different TitleBlock styles and the expected output for testing @see testRender.
     *
     * @return array
     */
    public function provideTestRender()
    {
        return array(
            array(TitleBlock::STYLE_SUCCESS, '<fg=black;bg=green>', '<fg=black;bg=green> [OK] Test message'),
            array(TitleBlock::STYLE_ERRORED_SUCCESS, '<fg=black;bg=yellow>', '<fg=black;bg=yellow> [OK, but with errors] Test message'),
            array(TitleBlock::STYLE_FAILURE, '<fg=white;bg=red>', '<fg=white;bg=red> [FAILURE] Test message'),
        );
    }
}
