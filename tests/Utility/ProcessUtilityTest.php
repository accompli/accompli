<?php

namespace Accompli\Test\Utility;

use Accompli\Utility\ProcessUtility;
use PHPUnit_Framework_TestCase;

/**
 * ProcessUtilityTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class ProcessUtilityTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if ProcessUtility::escapeArguments returns the expected escaped string for $arguments.
     *
     * @dataProvider provideTestEscapeArguments
     *
     * @param string      $expected
     * @param array       $arguments
     * @param string|null $command
     */
    public function testEscapeArguments($expected, array $arguments, $command = null)
    {
        $this->assertSame($expected, ProcessUtility::escapeArguments($arguments, $command));
    }

    /**
     * Tests if ProcessUtility::escapeArgument returns the expected escaped string.
     *
     * @dataProvider provideTestEscapeArgument
     *
     * @param string $expected
     * @param string $argument
     */
    public function testEscapeArgument($expected, $argument)
    {
        $this->assertSame($expected, ProcessUtility::escapeArgument($argument));
    }

    /**
     * Returns an array with test cases for @see testEscapeArguments.
     *
     * @return array
     */
    public function provideTestEscapeArguments()
    {
        return array(
            array('"argument" "argument with spaces"', array('argument', 'argument with spaces')),
            array('"argument" "argument with spaces"', array('argument', 'argument with spaces', array('ignored'))),
            array('"--option=optionvalue" "--option=optionvalue2"', array('--option' => array('optionvalue', 'optionvalue2'))),
            array('"--option-without-value"', array('--option-without-value' => null)),
            array('"argument" "--option=optionvalue" "--option=optionvalue2" "another-argument"', array('argument', '--option' => array('optionvalue', 'optionvalue2'), 'another-argument')),
            array('"/usr/bin/command" "--option=optionvalue" "--option=optionvalue2"', array('--option' => array('optionvalue', 'optionvalue2')), '/usr/bin/command'),
        );
    }

    /**
     * Returns an array with test cases for @see testEscapeArgument.
     *
     * @return array
     */
    public function provideTestEscapeArgument()
    {
        return array(
            array('"argument"', 'argument'),
            array('"argument with spaces"', 'argument with spaces'),
            array('"argument \"with\" quotes"', 'argument "with" quotes'),
            array('"argument with slashes\\\"', 'argument with slashes\\'),
            array('^%"environment variable"^%', '%environment variable%'),
        );
    }
}
