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
    ->setCacheFile(__DIR__ . '/dev/.php-cs-fixer.cache')
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS' => true,
        '@PHP8x2Migration' => true,
        'binary_operator_spaces' => ['default' => 'single_space'],
        'blank_line_before_statement' => [
            'statements' => ['break', 'continue', 'declare', 'return', 'throw'],
        ],
        'class_attributes_separation' => [
            'elements' => [
                'method' => 'one',
                'trait_import' => 'none',
            ],
        ],
        'trailing_comma_in_multiline' => [
            'elements' => ['arguments', 'array_destructuring', 'arrays', 'match', 'parameters'],
        ],
        'declare_strict_types' => true,
        'dir_constant' => true,
        'final_internal_class' => true,
        'fully_qualified_strict_types' => ['import_symbols' => true],
        'get_class_to_class_keyword' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'is_null' => true,
        'method_chaining_indentation' => true,
        'modernize_strpos' => true,
        'modernize_types_casting' => true,
        'native_function_invocation' => ['include' => ['@all'], 'scope' => 'namespaced'],
        'no_alias_functions' => true,
        'no_empty_phpdoc' => true,
        'no_extra_blank_lines' => ['tokens' => ['curly_brace_block', 'extra']],
        'no_superfluous_elseif' => true,
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true],
        'no_unneeded_control_parentheses' => true,
        'no_unneeded_final_method' => true,
        'no_unneeded_import_alias' => true,
        'no_unused_imports' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'ordered_class_elements' => [
            'case_sensitive' => true,
            'order' => [
                'use_trait',
                'case',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public',
                'property_protected',
                'property_private',
                'construct',
                'method_public',
                'method_protected',
                'method_private',
            ],
            'sort_algorithm' => 'alpha',
        ],
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
            'imports_order' => ['class', 'function', 'const'],
        ],
        'php_unit_construct' => true,
        'php_unit_data_provider_static' => true,
        'php_unit_dedicate_assert' => true,
        'php_unit_method_casing' => ['case' => 'camel_case'],
        'php_unit_test_case_static_method_calls' => ['call_type' => 'self'],
        'phpdoc_indent' => true,
        'phpdoc_order' => [
            'order' => [
                'template',
                'template-extends',
                'template-implements',
                'psalm-require-extends',
                'param',
                'return',
                'throws',
                'psalm-capabilities',
                'psalm-assert',
                'psalm-taint-escape',
                'see',
                'link',
                'deprecated',
                'api',
                'internal',
                'psalm-cli-only',
                'psalm-suppress',
            ],
        ],
        'phpdoc_scalar' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_trim' => true,
        'phpdoc_types_order' => ['null_adjustment' => 'always_last', 'sort_algorithm' => 'none'],
        'protected_to_private' => true,
        'return_assignment' => true,
        'self_accessor' => true,
        'self_static_accessor' => true,
        'simplified_if_return' => true,
        'simplified_null_return' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => null],
    ])
    ->setFinder($finder);
