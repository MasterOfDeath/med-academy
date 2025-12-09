<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/controllers')
    ->in(__DIR__ . '/models')
    ->in(__DIR__ . '/components')
    ->in(__DIR__ . '/widgets')
    ->in(__DIR__ . '/factories')
    ->in(__DIR__ . '/jobs')
    ->in(__DIR__ . '/repositories')
    ->in(__DIR__ . '/interfaces')
    ->in(__DIR__ . '/exceptions')
    ->name('*.php');

return (new PhpCsFixer\Config())
    ->setFinder($finder)
    ->setRules([
        '@PSR2' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => true,
        'no_unused_imports' => true,
        'not_operator_with_successor_space' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],
        'phpdoc_scalar' => true,
        'unary_operator_spaces' => true,
        'binary_operator_spaces' => true,
        'blank_line_before_statement' => [
            'statements' => ['break', 'continue', 'declare', 'return', 'throw', 'try'],
        ],
        'phpdoc_separation' => true,
        'phpdoc_align' => true,
        'phpdoc_order' => true,
        'phpdoc_trim' => true,
        'phpdoc_trim_consecutive_blank_line_separation' => true,
        'single_trait_insert_per_statement' => true,
        'no_superfluous_phpdoc_tags' => true,
    ])
    ->setRiskyAllowed(true);
