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

use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/controllers',
        __DIR__ . '/sql',
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/sendynl.php',
    ])
    ->withConfiguredRule(EncapsedStringsToSprintfRector::class, [
        EncapsedStringsToSprintfRector::ALWAYS => false,
    ]);
