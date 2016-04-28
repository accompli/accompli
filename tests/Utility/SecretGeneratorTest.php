<?php

namespace Accompli\Test\Utility;

use Accompli\Utility\SecretGenerator;
use PHPUnit_Framework_TestCase;

/**
 * Unit test for the secret generator.
 *
 * @author Ron Rademaker
 */
class SecretGeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if generated values are not regenerated within the same process.
     */
    public function testGeneratedValuesAreNotRegenerated()
    {
        $generator = new SecretGenerator();

        $generated = $generator->generate('foobar');

        $this->assertNotEmpty($generated);
        $this->assertEquals($generated, $generator->generate('foobar'));
        $this->assertNotEquals($generated, $generator->generate('baz'));
    }
}
