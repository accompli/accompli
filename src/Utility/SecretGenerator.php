<?php

namespace Accompli\Utility;

/**
 * Generatator to generate secrets.
 *
 * @author Ron Rademaker
 */
class SecretGenerator implements ValueGeneratorInterface
{
    /**
     * Generated values to prevent different secrets for the same key.
     *
     * @var array
     */
    private $generatedValues = array();

    /**
     * {@inheritdoc}
     */
    public function generate($key)
    {
        if (!array_key_exists($key, $this->generatedValues)) {
            $this->generatedValues[$key] = sha1(uniqid());
        }

        return $this->generatedValues[$key];
    }
}
