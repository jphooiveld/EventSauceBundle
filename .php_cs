<?php

declare(strict_types=1);

/** @noinspection ALL */
$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('vendor')
    ->exclude('tools')
    ->notPath('DependencyInjection/Configuration.php')
    ->name('*.php');

return PhpCsFixer\Config::create()
    ->setLineEnding("\n")
    ->setRules([
        '@PSR2'                                     => true,
        //        '@Symfony'                                  => true,
        //        '@Symfony:risky'                            => true,
        '@PHPUnit75Migration:risky'                 => true,
        'binary_operator_spaces'                    => ['align_double_arrow' => true, 'align_equals' => true],
        'blank_line_after_namespace'                => true,
        'braces'                                    => true,
        'class_definition'                          => true,
        'no_extra_consecutive_blank_lines'          => true,
        'no_multiline_whitespace_before_semicolons' => true,
        'method_chaining_indentation'               => true,
        'declare_strict_types'                      => true,
        'elseif'                                    => true,
        'encoding'                                  => true,
        'final_class'                               => true,
        'full_opening_tag'                          => true,
        'no_closing_tag'                            => true,
        'no_empty_statement'                        => true,
        'no_empty_phpdoc'                           => true,
        'no_empty_comment'                          => true,
        'array_indentation'                         => true,
        'array_syntax'                              => ['syntax' => 'short'],
        'no_short_echo_tag'                         => true,
        'no_spaces_around_offset'                   => true,
        'no_unused_imports'                         => true,
        'no_whitespace_before_comma_in_array'       => true,
        'not_operator_with_successor_space'         => false,
        'not_operator_with_space'                   => false,
        'ordered_imports'                           => ['sortAlgorithm' => 'alpha'],
        'trailing_comma_in_multiline_array'         => true,
        'trim_array_spaces'                         => true,
        'single_quote'                              => true,
        'single_blank_line_at_eof'                  => false,
        'visibility_required'                       => false,
        'linebreak_after_opening_tag'               => true,
        'strict_param'                              => true,
        'strict_comparison'                         => true,
        'is_null'                                   => true,
        'yoda_style'                                => ['always_move_variable' => false, 'equal' => false, 'identical' => false, 'less_and_greater' => false],
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder);
