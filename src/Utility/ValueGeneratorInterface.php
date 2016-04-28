<?php

namespace Accompli\Utility;

/**
 * Interface defining a generator for values.
 *
 * @author Ron Rademaker
 */
interface ValueGeneratorInterface
{
    /**
     * Generate a value for $key.
     *
     * @param string $key
     *
     * @return string
     */
    public function generate($key);
}
