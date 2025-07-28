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

/**
 * In some cases you should not drop the tables.
 * Maybe the merchant will just try to reset the module
 * but does not want to loose all of the data associated to the module.
 */
$sql = [
    'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'sendy_shipment`',
    'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'sendy_package`',
    'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'sendy_carrier_config`',
];

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
