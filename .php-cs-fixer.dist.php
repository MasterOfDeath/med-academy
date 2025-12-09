<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude(['vendor', 'runtime', 'web/assets', 'node_modules', '.docker'])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new PhpCsFixer\Config())
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setCacheFile(__DIR__ . '/runtime/.php-cs-fixer.cache')
    ->setRules([
        '@PSR12' => true,
        
        'array_syntax' => ['syntax' => 'short'],
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],
        
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,

        'not_operator_with_successor_space' => false,
        'unary_operator_spaces' => true,
        'binary_operator_spaces' => true,
        'concat_space' => ['spacing' => 'one'],
        'ternary_operator_spaces' => true,

        'phpdoc_scalar' => true,
        'phpdoc_separation' => true,
        'phpdoc_align' => ['align' => 'vertical'],
        'phpdoc_trim' => true,
        'no_superfluous_phpdoc_tags' => false,
        'no_empty_phpdoc' => true,

        'blank_line_before_statement' => [
            'statements' => ['break', 'continue', 'declare', 'return', 'throw', 'try'],
        ],
        'single_quote' => true,
        'no_extra_blank_lines' => [
            'tokens' => ['extra', 'throw', 'use'],
        ],
        'cast_spaces' => ['space' => 'single'],
    ]);
