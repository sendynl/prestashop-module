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

namespace Sendy\PrestaShop\Support;

class Arr
{
    /**
     * @template TValue
     * @template TKey of array-key
     *
     * @param array<TKey, TValue> $array
     * @param callable(TValue, TKey): bool $callback
     *
     * @return TValue|null
     *
     * @see https://www.php.net/manual/en/function.array-find.php
     */
    public static function find(array $array, callable $callback)
    {
        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }

        return null;
    }
}
