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

if (!defined('_PS_VERSION_')) {
    exit;
}

final class ProcessingMethod extends StringBackedEnum
{
    public const PrestaShop = 'prestashop';
    public const Sendy = 'sendy';
}
