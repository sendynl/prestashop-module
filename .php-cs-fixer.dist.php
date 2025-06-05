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
        ];
    }
};

/** @var Symfony\Component\Finder\Finder $finder */
$finder = $config->setUsingCache(true)->getFinder();
$finder->in(__DIR__)->ignoreVCSIgnored(true);

return $config;
