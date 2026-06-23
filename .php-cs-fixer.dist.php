<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([
        __DIR__ . '/bin',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->append([
        __DIR__ . '/.php-cs-fixer.dist.php',
    ])
    ->exclude('Support/_generated');

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS' => true,
        '@PHP8x2Migration' => true,
        'binary_operator_spaces' => ['default' => 'single_space'],
        'trailing_comma_in_multiline' => [
            'elements' => ['arguments', 'array_destructuring', 'arrays', 'match', 'parameters'],
        ],
        'fully_qualified_strict_types' => ['import_symbols' => true],
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'native_function_invocation' => ['include' => ['@all'], 'scope' => 'namespaced'],
        'no_unused_imports' => true,
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
            'imports_order' => ['class', 'function', 'const'],
        ],
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => null],
    ])
    ->setFinder($finder);
