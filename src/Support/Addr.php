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

namespace Sendy\PrestaShop\Support;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Addr
{
    /**
     * Parse an address line into a street, number, and addition.
     *
     * @return array{street: string, number: ?string, addition: ?string}
     */
    public static function parseAddress(string $address): array
    {
        $tokens = explode(' ', $address);

        // Find the index of the first token from the right that starts with a digit
        $numberIndex = false;
        for ($i = count($tokens) - 1; $i >= 0; --$i) {
            if (isset($tokens[$i][0]) && is_numeric($tokens[$i][0])) {
                $numberIndex = $i;
                break;
            }
        }

        if ($numberIndex === false) {
            return ['street' => $address, 'number' => null, 'addition' => null];
        }

        $street = trim(implode(' ', array_slice($tokens, 0, $numberIndex)));
        $number = trim(implode(' ', array_slice($tokens, $numberIndex)));

        if (trim($street) === '') {
            $street = $number;
            $number = '';
        }

        $extractedNumber = self::parseHouseNumber($number);

        return [
            'street' => $street,
            'number' => $extractedNumber['number'],
            'addition' => $extractedNumber['addition'],
        ];
    }

    /**
     * Parse a house number into its numeric part and any addition.
     *
     * @return array{number: ?string, addition: ?string}
     */
    public static function parseHouseNumber(string $number): array
    {
        $addition = '';

        if (!ctype_digit($number)) {
            $addition = ltrim($number, '0123456789');
            $number = substr($number, 0, 0 - strlen($addition));
        }

        return [
            'number' => trim($number) ?: null,
            'addition' => trim($addition) ?: null,
        ];
    }
}
