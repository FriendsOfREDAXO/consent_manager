<?php

declare(strict_types=1);

namespace Redaxo\PhpCsFixerConfig;

use PhpCsFixer\ConfigInterface;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;
use PhpCsFixerCustomFixers\Fixer\PhpdocSingleLineVarFixer;
use PhpCsFixerCustomFixers\Fixers;
use Redaxo\PhpCsFixerConfig\Fixer\NoSemicolonBeforeClosingTagFixer;
use Redaxo\PhpCsFixerConfig\Fixer\StatementIndentationFixer;

class Config extends \PhpCsFixer\Config
{
    /** @var array<string, bool|array<mixed>> */
    private array $defaultRules;

    /**
     * @deprecated use `Config::redaxo5()` or `Config::redaxo6()` instead
     */
    public function __construct(string $name = 'REDAXO 5', string $phpMigration = '81', string $phpMigrationRisky = '80')
    {
        parent::__construct($name);

        $this->setUsingCache(true);
        $this->setParallelConfig(ParallelConfigFactory::detect());
        $this->setRiskyAllowed(true);
        $this->registerCustomFixers(new Fixers());
        $this->registerCustomFixers([
            new NoSemicolonBeforeClosingTagFixer(),
            new StatementIndentationFixer(),
        ]);

        $this->defaultRules = [
            '@PER-CS2.0' => true,
            '@PER-CS2.0:risky' => true,
            '@Symfony' => true,
            '@Symfony:risky' => true,
            '@PHP' . $phpMigration . 'Migration' => true,
            '@PHP' . $phpMigrationRisky . 'Migration:risky' => true,
            '@PHPUnit100Migration:risky' => true,

            'array_indentation' => true,
            'blank_line_before_statement' => false,
            'comment_to_phpdoc' => true,
            'concat_space' => ['spacing' => 'one'],
            'declare_strict_types' => false,
            'echo_tag_syntax' => ['format' => 'short'],
            'fully_qualified_strict_types' => ['import_symbols' => true],
            'global_namespace_import' => [
                'import_constants' => true,
                'import_functions' => true,
                'import_classes' => true,
            ],
            'heredoc_to_nowdoc' => true,
            'method_argument_space' => ['on_multiline' => 'ignore'],
            'multiline_comment_opening_closing' => true,
            'multiline_promoted_properties' => ['keep_blank_lines' => true],
            'native_constant_invocation' => [
                'scope' => 'namespaced',
                'strict' => false,
            ],
            'no_alternative_syntax' => false,
            'no_blank_lines_after_phpdoc' => false,
            'no_superfluous_elseif' => true,
            'no_superfluous_phpdoc_tags' => [
                'allow_mixed' => true,
                'remove_inheritdoc' => true,
            ],
            'no_unreachable_default_argument_value' => true,
            'no_useless_else' => true,
            'no_useless_return' => true,
            'ordered_class_elements' => ['order' => [
                'use_trait',
                'case',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property',
                'construct',
                'phpunit',
                'method',
            ]],
            'php_unit_internal_class' => true,
            'php_unit_test_case_static_method_calls' => ['call_type' => 'self'],
            'phpdoc_align' => false,
            'phpdoc_array_type' => true,
            'phpdoc_no_package' => false,
            'phpdoc_order' => true,
            'phpdoc_separation' => false,
            'phpdoc_to_comment' => false,
            'phpdoc_var_annotation_correct_order' => true,
            'psr_autoloading' => false,
            'semicolon_after_instruction' => false,
            'single_line_empty_body' => true,
            'single_line_throw' => false,
            'statement_indentation' => false,
            'static_lambda' => true,
            'string_implicit_backslashes' => ['single_quoted' => 'ignore'],
            'trailing_comma_in_multiline' => [
                'after_heredoc' => true,
                'elements' => ['arguments', 'arrays', 'match', 'parameters'],
            ],
            'use_arrow_functions' => false,
            'void_return' => false,

            PhpdocSingleLineVarFixer::name() => true,

            'Redaxo/no_semicolon_before_closing_tag' => true,
            'Redaxo/statement_indentation' => true,
        ];

        $this->setRules([]);
    }

    public static function redaxo5(): self
    {
        return new self();
    }

    public static function redaxo6(): self
    {
        $config = new self('REDAXO 6', '84', '82');

        $config->defaultRules['general_phpdoc_annotation_remove'] = [
            'annotations' => ['author', 'package'],
        ];

        $config->setRules([]);

        return $config;
    }

    public function setRules(array $rules): ConfigInterface
    {
        return parent::setRules(array_merge($this->defaultRules, $rules));
    }
}
