<?php
/**
 * This file is part of the Sendy PrestaShop module - https://sendy.nl
 *
 * @author Sendy B.V.
 * @copyright Sendy B.V.
 * @license https://github.com/sendynl/prestashop-module/blob/master/LICENSE MIT
 *
 * @see https://github.com/sendynl/prestashop-module
 */
declare(strict_types=1);
$config = new class extends PrestaShop\CodingStandards\CsFixer\Config {
    public function getRules(): array
    {
        return [
            ...parent::getRules(),
            'declare_strict_types' => true,
            'global_namespace_import' => [
                'import_classes' => false,
                'import_constants' => false,
                'import_functions' => false,
            ],
            'no_unused_imports' => true,
            'blank_line_after_opening_tag' => false,
            'header_comment' => [
                'comment_type' => 'PHPDoc',
                'header' => <<<'HEADER'
                    This file is part of the Sendy PrestaShop module - https://sendy.nl

                    @author Sendy B.V.
                    @copyright Sendy B.V.
                    @license https://github.com/sendynl/prestashop-module/blob/master/LICENSE MIT

                    @see https://github.com/sendynl/prestashop-module
                    HEADER,
                'location' => 'after_open',
                'separate' => 'none',
            ],
            'trailing_comma_in_multiline' => [
                // Only target arrays, as trailing commas in function calls are not supported in PHP 7.4.
                'elements' => ['arrays'],
            ],
        ];
    }
};

/** @var Symfony\Component\Finder\Finder $finder */
$finder = $config->setUsingCache(true)->getFinder();
$finder->in(__DIR__)->append([__FILE__])->ignoreVCSIgnored(true);

return $config;
