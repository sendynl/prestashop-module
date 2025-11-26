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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * In some cases you should not drop the tables.
 * Maybe the merchant will just try to reset the module
 * but does not want to loose all of the data associated to the module.
 */

// The 'sendynl_carrier_config' table is intentionally not dropped to avoid confusion during module reset,
// since uninstalling the module does not remove carriers.

$sql = [
    'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'sendynl_shipment`',
    'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'sendynl_package`',
    'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'sendynl_cart_parcel_shop`',
];

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
