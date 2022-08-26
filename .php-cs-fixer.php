<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude(['vendor'])
    ->in(__DIR__);

return (new PhpCsFixer\Config())
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR1' => true,
        '@PSR2' => true,
        '@PSR12' => true,
        '@Symfony' => true,
        '@PhpCsFixer' => true,
        '@PHP80Migration' => true,

        // Escape implicit backslashes in strings and heredocs to ease the understanding of which are special chars interpreted by PHP and which not.
        'escape_implicit_backslashes' => [
            'double_quoted' => false,
            'heredoc_syntax' => false,
            'single_quoted' => false,
        ],
        // Converts implicit variables into explicit ones in double-quoted strings or heredoc syntax.
        'explicit_string_variable' => false,
        // Pre- or post-increment and decrement operators should be used if possible.
        'increment_style' => ['style' => 'post'],
        // Forbid multi-line whitespace before the closing semicolon or move the semicolon to the new line for chained calls.
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
        // Master functions shall be used instead of aliases.
        'no_alias_functions' => true,
        // Operators - when multiline - must always be at the beginning or at the end of the line.
        'operator_linebreak' => ['only_booleans' => true, 'position' => 'beginning'],
        // Orders the elements of classes/interfaces/traits.
        'ordered_class_elements' => true,
        // No alias PHPDoc tags should be used. Only replace `var` and `link`
        'phpdoc_no_alias_tag' => ['replacements' => ['type' => 'var', 'link' => 'see']],
        // Docblocks should only be used on structural elements.
        'phpdoc_to_comment' => false,
        // PHPDoc summary should end in either a full stop, exclamation mark, or question mark.
        'phpdoc_summary' => false,
        // Single-line comments and multi-line comments with only one line of actual content should use the `//` syntax. Disabled to allow for `/** @noInspection ... */`
        'single_line_comment_style' => false,
        // Cast shall be used, not `settype`.
        'set_type_to_cast' => true,
        // Use the Elvis operator `?:` where possible.
        'ternary_to_elvis_operator' => true,
        // Write conditions in Yoda style (`true`), non-Yoda style (`['equal' => false, 'identical' => false, 'less_and_greater' => false]`) or ignore those conditions (`null`) based on configuration.
        'yoda_style' => false,
    ]);
