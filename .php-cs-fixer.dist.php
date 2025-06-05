<?php

declare(strict_types=1);

/**
 * This file is part of the Sendy PrestaShop module - https://sendy.nl
 *
 * @author Sendy B.V.
 * @copyright Sendy B.V.
 * @license https://github.com/sendynl/prestashop-module/blob/master/LICENSE MIT
 *
 * @see https://github.com/sendynl/prestashop-module
 */

$config = new class extends PrestaShop\CodingStandards\CsFixer\Config {
    public function getRules(): array
    {
        return [
            ...parent::getRules(),
            'declare_strict_types' => true,
            'global_namespace_import' => true,
            'header_comment' => [
                'comment_type' => 'PHPDoc',
                'header' => <<<'HEADER'
                    This file is part of the Sendy PrestaShop module - https://sendy.nl

                    @author Sendy B.V.
                    @copyright Sendy B.V.
                    @license https://github.com/sendynl/prestashop-module/blob/master/LICENSE MIT

                    @see https://github.com/sendynl/prestashop-module
                    HEADER,
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
