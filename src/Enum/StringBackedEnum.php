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

namespace Sendy\PrestaShop\Enum;

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use PrestaShop\PrestaShop\Core\Util\String\StringModifier;

if (!defined('_PS_VERSION_')) {
    exit;
}

abstract class StringBackedEnum
{
    /**
     * @return list<string>
     */
    public static function values(): array
    {
        $reflection = new \ReflectionClass(static::class);

        return array_values($reflection->getConstants());
    }

    /**
     * @return array<string, string>
     */
    public static function choices(): array
    {
        $constants = (new \ReflectionClass(static::class))->getConstants();
        $choices = [];

        foreach ($constants as $value) {
            $choices[static::getDescription($value)] = $value;
        }

        return $choices;
    }

    public static function getDescription(string $value): string
    {
        $reflection = new \ReflectionClass(static::class);
        $constants = $reflection->getConstants();

        foreach ($constants as $name => $constantValue) {
            if ($constantValue === $value) {
                $key = "{$reflection->getShortName()}::{$name}";
                $translated = SymfonyContainer::getInstance()->get('translator')->trans($key, [], 'Modules.Sendy.Enum');

                if ($translated !== $key) {
                    return $translated;
                }

                return (new StringModifier())->splitByCamelCase($name);
            }
        }

        throw new \InvalidArgumentException(sprintf('Value "%s" is not a valid constant of %s', $value, static::class));
    }
}
