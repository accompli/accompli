<?php

namespace Accompli\Test;

use Accompli\Console\Formatter\OutputFormatter;
use PHPUnit_Framework_TestCase;

/**
 * OutputFormatterTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class OutputFormatterTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if constructing a new OutputFormatter sets the styles.
     */
    public function testConstruct()
    {
        $formatter = new OutputFormatter();

        $this->assertTrue($formatter->hasStyle('event-name'));
        $this->assertTrue($formatter->hasStyle('event-task-name'));
        $this->assertTrue($formatter->hasStyle('event-task-action-in_progress'));
        $this->assertTrue($formatter->hasStyle('event-task-action-failed'));
        $this->assertTrue($formatter->hasStyle('event-task-action-completed'));
    }
}
