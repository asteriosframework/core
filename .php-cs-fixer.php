<?php

$finder = PhpCsFixer\Finder::create()
    ->in([__DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/spec']);

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@PSR12' => true,
    'full_opening_tag' => true,
    'single_blank_line_at_eof' => false,
    'blank_line_after_opening_tag' => false,
    'curly_braces_position' => [
        'control_structures_opening_brace' => 'next_line_unless_newline_at_signature_end',
    ],
    'control_structure_continuation_position' => ['position' => 'next_line'],
])->setFinder($finder);
