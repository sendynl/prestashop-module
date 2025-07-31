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

$prefix = _DB_PREFIX_;
$engine = _MYSQL_ENGINE_;

$sql = [];

$sql[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$prefix}sendy_shipment` (
    `id_sendy_shipment` CHAR(36) NOT NULL PRIMARY KEY,
    `id_order` INT UNSIGNED NOT NULL,
    INDEX `order` (`id_order`)
) ENGINE={$engine} DEFAULT CHARSET=utf8;
SQL;

$sql[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$prefix}sendy_package` (
    `id_sendy_package` CHAR(36) NOT NULL PRIMARY KEY,
    `id_sendy_shipment` CHAR(36) NOT NULL,
    `tracking_number` VARCHAR(255) NOT NULL,
    `tracking_url` VARCHAR(255) NULL,
    INDEX `shipment` (`id_sendy_shipment`)
) ENGINE={$engine} DEFAULT CHARSET=utf8;
SQL;

$sql[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$prefix}sendy_carrier_config` (
    `id_sendy_carrier_config` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `id_reference` INT UNSIGNED NOT NULL,
    `parcel_shop_delivery_enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `parcel_shop_carrier` VARCHAR(255) NULL,
    UNIQUE INDEX `id_reference` (`id_reference`)
) ENGINE={$engine} DEFAULT CHARSET=utf8;
SQL;

$sql[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$prefix}sendy_cart_parcel_shop` (
    `id_sendy_cart_parcel_shop` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `id_cart` INT UNSIGNED NOT NULL,
    `id_reference` INT UNSIGNED NOT NULL,
    `parcel_shop_id` VARCHAR(255) NOT NULL,
    `parcel_shop_name` VARCHAR(255) NOT NULL,
    `parcel_shop_address` TEXT NOT NULL,
    UNIQUE INDEX `id_cart` (`id_cart`)
) ENGINE={$engine} DEFAULT CHARSET=utf8;
SQL;

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
