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

namespace Sendy\PrestaShop\Installer;

use Configuration;
use Db;
use Employee;
use PrestaShopLogger;
use Tools;

/**
 * Installs the system user that is needed when the webhook creates database records that need an employee ID, e.g. when
 * changing the order status to 'Delivered' which causes a stock movement to be created.
 */
class SystemUser
{
    public static function install(): bool
    {
        PrestaShopLogger::addLog('Sendy - Installing system user');

        $employee = new Employee();

        if (Employee::employeeExists('prestashop-automation-user@sendy.nl')) {
            $employee->getByEmail('prestashop-automation-user@sendy.nl', null, false);
        }

        $employee->firstname = 'Sendy';
        $employee->lastname = 'Automation';
        $employee->email = 'prestashop-automation-user@sendy.nl';
        $employee->setWsPasswd(Tools::passwdGen(40));
        $employee->active = 0;
        $employee->id_profile = (int) Db::getInstance()
            ->getValue('SELECT id_profile FROM ' . _DB_PREFIX_ . 'profile ORDER BY id_profile ASC');
        $employee->id_lang = (int) Configuration::get('PS_LANG_DEFAULT');

        $employee->save();

        Configuration::updateValue('SENDY_SYSTEM_USER_ID', $employee->id);

        PrestaShopLogger::addLog("Sendy - Installed system user {$employee->id}");

        return true;
    }

    public static function exists(): bool
    {
        $id = Configuration::get('SENDY_SYSTEM_USER_ID');

        if (!$id) {
            return false;
        }

        $employee = new Employee($id);

        return (bool) $employee->id;
    }

    public static function ensureInstalled(): bool
    {
        if (self::exists()) {
            return true;
        }

        return self::install();
    }

    public static function uninstall(): bool
    {
        $id = Configuration::get('SENDY_SYSTEM_USER_ID');

        if (!$id) {
            return true;
        }

        $employee = new Employee($id);
        $employee->delete();

        Configuration::deleteByName('SENDY_SYSTEM_USER_ID');

        return true;
    }
}
