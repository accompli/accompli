<?php

namespace Accompli\Utility;

/**
 * ProcessUtility.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class ProcessUtility
{
    /**
     * Returns a string with escaped option values and arguments from the supplied arguments array.
     *
     * @param array       $arguments
     * @param string|null $command
     *
     * @return string
     */
    public static function escapeArguments(array $arguments, $command = null, $optionSeparator = '=')
    {
        $processedArguments = array();
        foreach ($arguments as $key => $value) {
            if (is_string($key) && substr($key, 0, 1) === '-') {
                if (is_array($value) === false) {
                    $value = array($value);
                }

                foreach ($value as $optionValue) {
                    if ($optionValue === null) {
                        $processedArguments[] = $key;
                    } elseif (is_string($optionValue)) {
                        $processedArguments[] = trim(sprintf('%s%s%s', $key, $optionSeparator, $optionValue));
                    }
                }
            } elseif (is_scalar($value)) {
                $processedArguments[] = $value;
            }
        }

        if ($command !== null) {
            array_unshift($processedArguments, $command);
        }

        return implode(' ', array_map(array(__CLASS__, 'escapeArgument'), $processedArguments));
    }

    /**
     * Escapes an argument that is valid for both Unix and Windows operating systems.
     *
     * @param string $argument
     *
     * @return string
     */
    public static function escapeArgument($argument)
    {
        $escapedArgument = '';
        $quote = false;
        foreach (preg_split('/(")/', $argument, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE) as $part) {
            if ($part === '"') {
                $escapedArgument .= '\\"';
            } elseif (self::isSurroundedBy($part, '%')) {
                // Avoid environment variable expansion
                $escapedArgument .= '^%"'.substr($part, 1, -1).'"^%';
            } else {
                // escape trailing backslash
                if (substr($part, -1) === '\\') {
                    $part .= '\\';
                }
                $quote = true;
                $escapedArgument .= $part;
            }
        }
        if ($quote) {
            $escapedArgument = '"'.$escapedArgument.'"';
        }

        return $escapedArgument;
    }

    /**
     * Returns true when the argument is surrounded by character.
     *
     * @param string $argument
     * @param string $character
     *
     * @return bool
     */
    private static function isSurroundedBy($argument, $character)
    {
        return 2 < strlen($argument) && $character === $argument[0] && $character === $argument[strlen($argument) - 1];
    }
}
