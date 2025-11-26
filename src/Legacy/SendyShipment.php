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

namespace Sendy\PrestaShop\Legacy;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SendyShipment extends \ObjectModel
{
    public $id_sendynl_shipment;

    public $id_order;

    public static $definition = [
        'table' => 'sendynl_shipment',
        'primary' => 'id_sendynl_shipment',
        'fields' => [
            'id_sendynl_shipment' => ['type' => self::TYPE_STRING, 'required' => true],
            'id_order' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
        ],
    ];

    /**
     * @throws \PrestaShopException
     */
    public static function existsForOrderId(int $orderId): bool
    {
        return (bool) \Db::getInstance()->getValue(
            'SELECT 1 FROM `' . _DB_PREFIX_ . 'sendynl_shipment` WHERE `id_order` = ' . $orderId
        );
    }

    public static function getByUuid(string $uuid): ?self
    {
        $shipment = new self($uuid);

        if (is_null($shipment->id_sendynl_shipment)) {
            return null;
        }

        return $shipment;
    }

    public static function deleteByUuid(string $id_sendynl_shipment): void
    {
        \Db::getInstance()->delete(
            'sendynl_shipment',
            '`id_sendynl_shipment` = \'' . pSQL($id_sendynl_shipment) . '\''
        );
    }
}
