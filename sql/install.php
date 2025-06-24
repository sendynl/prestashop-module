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
    `id_order` INT UNSIGNED NOT NULL
) ENGINE={$engine} DEFAULT CHARSET=utf8;
SQL;

$sql[] = <<<SQL
CREATE INDEX `order` ON `{$prefix}sendy_shipment` (`id_order`);
SQL;

$sql[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$prefix}sendy_package` (
    `id_sendy_package` CHAR(36) NOT NULL PRIMARY KEY,
    `id_sendy_shipment` CHAR(36) NOT NULL,
    `tracking_number` VARCHAR(255) NOT NULL
) ENGINE={$engine} DEFAULT CHARSET=utf8;
SQL;

$sql[] = <<<SQL
CREATE INDEX `shipment` ON `{$prefix}sendy_package` (`id_sendy_shipment`);
SQL;

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
