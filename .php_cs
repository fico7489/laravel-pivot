<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

/*
 * Define folders to fix
 */
$finder = Finder::create()
    ->in([
        __DIR__ .'/src',
        __DIR__ .'/tests',
    ]);
;

/*
 * Do the magic
 */
return Config::create()
    ->setUsingCache(false)
    ->setRules([
        '@PSR2'              => true,
        '@Symfony'           => true,
    ])
    ->setFinder($finder)
;