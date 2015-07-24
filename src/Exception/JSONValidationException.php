<?php

namespace Accompli\Exception;

use Exception;

/**
 * JSONValidationException
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 * @package Accompli\Exception
 **/
class JSONValidationException extends Exception
{
    /**
     * The array with JSON validation errors
     *
     * @access private
     * @var array
     **/
    private $errors = array();

    /**
     * __construct
     *
     * Constructs a new JSONValidationException instance
     *
     * @access public
     * @param  string    $message
     * @param  array     $errors
     * @param  Exception $previous
     * @return null
     **/
    public function __construct($message, array $errors = array(), Exception $previous = null)
    {
        $this->errors = $errors;

        parent::__construct($message, 0, $previous);
    }

    /**
     * getErrors
     *
     * Returns the JSON validation errors
     *
     * @access public
     * @return array
     **/
    public function getErrors()
    {
        return $this->errors;
    }
}
