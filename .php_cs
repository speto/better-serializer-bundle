<?php

$config = PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        'array_syntax'      => [
            'syntax' => 'short',
        ],
        'no_useless_else'   => true,
        'no_useless_return' => true,
        'strict_comparison' => true,
        'strict_param'      => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude('vendor')
            ->in(__DIR__)
    );

return $config;
