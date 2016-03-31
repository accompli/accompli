<?php

namespace Accompli\Test\Console\Formatter;

use Accompli\Console\Formatter\OutputFormatter;
use PHPUnit_Framework_TestCase;
use Psr\Log\LogLevel;

/**
 * OutputFormatterTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class OutputFormatterTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if constructing a new OutputFormatter sets the styles.
     *
     * @dataProvider provideTestConstruct
     *
     * @param string $styleName
     */
    public function testConstruct($styleName)
    {
        $formatter = new OutputFormatter();

        $this->assertTrue($formatter->hasStyle($styleName));
    }

    /**
     * Returns an array with test cases for @see testConstruct.
     *
     * @return array
     */
    public function provideTestConstruct()
    {
        return array(
            array(LogLevel::EMERGENCY),
            array(LogLevel::CRITICAL),
            array(LogLevel::ALERT),
            array(LogLevel::ERROR),
            array(LogLevel::WARNING),
            array(LogLevel::NOTICE),
            array(LogLevel::INFO),
            array(LogLevel::DEBUG),
            array('event-name'),
            array('event-task-name'),
            array('event-task-action-in_progress'),
            array('event-task-action-failed'),
            array('event-task-action-completed'),
        );
    }
}
