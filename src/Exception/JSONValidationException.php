<?php

namespace Accompli\Exception;

use Exception;

/**
 * JSONValidationException.
 *
 * @author  Niels Nijens <nijens.niels@gmail.com>
 */
class JSONValidationException extends Exception
{
    /**
     * The array with JSON validation errors.
     *
     * @var array
     */
    private $errors = array();

    /**
     * Constructs a new JSONValidationException instance.
     *
     * @param string    $message
     * @param array     $errors
     * @param Exception $previous
     */
    public function __construct($message, array $errors = array(), Exception $previous = null)
    {
        $this->errors = $errors;

        parent::__construct($message, 0, $previous);
    }

    /**
     * Returns the JSON validation errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
