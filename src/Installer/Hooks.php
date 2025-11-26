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

class Hooks
{
    /**
     * Registers all hooks defined as methods in the main module class.
     *
     * @param \Sendynl $module the Sendy module instance
     */
    public static function install(\Sendynl $module): bool
    {
        \PrestaShopLogger::addLog('Sendy - Installing hooks');

        foreach (get_class_methods($module) as $method) {
            if (substr($method, 0, 4) !== 'hook') {
                continue;
            }

            if (!$module->registerHook(lcfirst(substr($method, 4)))) {
                return false;
            }
        }

        \PrestaShopLogger::addLog('Sendy - Installed hooks');

        return true;
    }
}
