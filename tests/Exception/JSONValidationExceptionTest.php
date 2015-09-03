<?php

namespace Accompli\Test;

use Accompli\Exception\JSONValidationException;
use PHPUnit_Framework_TestCase;

/**
 * JSONValidationExceptionTest
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 * @package Accompli\Test
 */
class JSONValidationExceptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests instantiation of JSONValidationException
     *
     * @access public
     */
    public function testConstruct()
    {
        $exception = new JSONValidationException("");

        $this->assertInstanceOf("Exception", $exception);
    }

    /**
     * Tests if JSONValidationException::getErrors returns an empty array by default
     *
     * @access public
     */
    public function testGetErrorsReturnsEmptyArrayByDefault()
    {
        $exception = new JSONValidationException("");

        $this->assertInternalType("array", $exception->getErrors());
        $this->assertEmpty($exception->getErrors());
    }

    /**
     * Tests if JSONValidationException::getErrors returns the array provided during instantion of JSONValidationException
     *
     * @access public
     */
    public function testGetErrorsReturnsArraySetWhenConstructing()
    {
        $errors = array("test");
        $exception = new JSONValidationException("", $errors);

        $this->assertSame($errors, $exception->getErrors());
    }
}
