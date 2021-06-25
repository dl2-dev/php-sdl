<?php declare(strict_types=1);

const GLOBAL_NAMESPACE_IMPORT = [
    'import_classes'   => true,
    'import_constants' => true,
    'import_functions' => false,
];

const NATIVE_FUNCTION_INVOCATION = [
    'include' => ['@compiler_optimized'],
    // 'include' => ['@all'],
    'scope'  => 'namespaced',
    'strict' => true,
];

const ORDERED_CLASS_ELEMENTS = [
    'sort_algorithm' => 'alpha',
    'order'          => [
        'use_trait',
        'constant_public',
        'constant_protected',
        'constant_private',
        'property_public_static',
        'property_protected_static',
        'property_private_static',
        'property_public',
        'property_protected',
        'property_private',
        'method_public_abstract',
        'method_protected_abstract',
        'construct',
        'destruct',
        'magic',
        'phpunit',
        'method_public',
        'method_protected',
        'method_private',
    ],
];

return (new PhpCsFixer\Config())
    ->setUsingCache(false)
    ->setRiskyAllowed(true)
    ->setHideProgress(true)
    ->setRules([
        '@DoctrineAnnotation'          => true,
        '@PHP74Migration'              => true,
        '@PhpCsFixer'                  => true,
        '@PhpCsFixer:risky'            => true,
        'binary_operator_spaces'       => ['default' => 'align_single_space'],
        'blank_line_after_opening_tag' => false,
        'cast_spaces'                  => ['space' => 'single'],
        'concat_space'                 => ['spacing' => 'one'],
        'declare_strict_types'         => true,
        'global_namespace_import'      => GLOBAL_NAMESPACE_IMPORT,
        'linebreak_after_opening_tag'  => false,
        'native_function_invocation'   => NATIVE_FUNCTION_INVOCATION,
        'ordered_class_elements'       => ORDERED_CLASS_ELEMENTS,
        'phpdoc_to_comment'            => false,
        'single_line_comment_style'    => ['comment_types' => ['hash']],
    ])
;
