<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude([
        'vendor',
        'runtime',
        'web/assets',
        'web/bundles',
        'console/migrations',
    ])
    ->notPath('yii')
    ->name('*.php')
    ->notName('*.html.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$config = new PhpCsFixer\Config();

return $config
    ->setRules([
        '@PSR12' => true,

        // Массивы: короткий синтаксис [] и запятая в конце многострочных массивов
        'array_syntax' => ['syntax' => 'short'],
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],
        'no_whitespace_before_comma_in_array' => true,
        'whitespace_after_comma_in_array' => true,

        // Импорты (Use): удаляем лишнее, сортируем по алфавиту
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'fully_qualified_strict_types' => true,
        'no_leading_import_slash' => true,

        // Чистота кода и пустые строки
        'no_extra_blank_lines' => [
            'tokens' => [
                'extra',
                'throw',
                'use',
            ],
        ],
        'concat_space' => ['spacing' => 'one'], // 'a' . 'b' вместо 'a'.'b'
        'binary_operator_spaces' => [
            'default' => 'single_space',
        ],

        // Очистка PHPDoc
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_indent' => true,
        'phpdoc_no_access' => true,
        'phpdoc_no_package' => true,
        'phpdoc_no_useless_inheritdoc' => true,
        'phpdoc_scalar' => true,
        'phpdoc_summary' => false,
        'phpdoc_trim' => true,
        'phpdoc_types' => true,
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true],

        'no_singleline_whitespace_before_semicolons' => true,
        'no_trailing_whitespace' => true,
        'single_quote' => true,
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setUsingCache(true);
