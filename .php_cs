<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->in(__DIR__);

return Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)
    ->fixers(array(
        "-psr0",
        "-concat_without_space",
        "-phpdoc_no_access",
        "-phpdoc_no_empty_return",
        "-phpdoc_no_package",
        "-phpdoc_scalar",
        "-phpdoc_separation",
        "-phpdoc_short_description",
        "concat_with_spaces",
        "ordered_use",
    ))
    ->finder($finder);
