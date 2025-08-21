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

namespace Sendy\PrestaShop\Installer;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @see \Sendy\PrestaShop\Repository\ConfigurationRepository
 */
class ConfigurationDefaults
{
    public static function install(): bool
    {
        \PrestaShopLogger::addLog('Sendy - Installing configuration defaults');

        \Configuration::updateGlobalValue('SENDY_PROCESSABLE_STATUS', \Configuration::get('PS_OS_PAYMENT'));
        \Configuration::updateGlobalValue('SENDY_STATUS_GENERATED', \Configuration::get('PS_OS_PREPARATION'));
        \Configuration::updateGlobalValue('SENDY_STATUS_PRINTED', \Configuration::get('PS_OS_SHIPPING'));
        \Configuration::updateGlobalValue('SENDY_STATUS_DELIVERED', \Configuration::get('PS_OS_DELIVERED'));

        \PrestaShopLogger::addLog('Sendy - Installed configuration defaults');

        return true;
    }
}
