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

namespace Sendy\PrestaShop\Legacy;

use Db;
use ObjectModel;

class SendyCartParcelShop extends ObjectModel
{
    public $id_sendy_cart_parcel_shop;
    public $id_cart;
    public $id_reference;
    public $parcel_shop_id;
    public $parcel_shop_name;
    public $parcel_shop_address;

    public static $definition = [
        'table' => 'sendy_cart_parcel_shop',
        'primary' => 'id_sendy_cart_parcel_shop',
        'fields' => [
            'id_sendy_cart_parcel_shop' => ['type' => self::TYPE_INT, 'auto_increment' => true],
            'id_cart' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'id_reference' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'parcel_shop_id' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true],
            'parcel_shop_name' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true],
            'parcel_shop_address' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true],
        ],
    ];

    /**
     * Get the parcel shop by cart ID.
     *
     * @param int $id_cart
     *
     * @return SendyCartParcelShop|null
     */
    public static function getByCartId(int $id_cart): ?SendyCartParcelShop
    {
        $prefix = _DB_PREFIX_;
        $sql = "SELECT * FROM `{$prefix}sendy_cart_parcel_shop` WHERE `id_cart` = {$id_cart}";
        $result = Db::getInstance()->getRow($sql);

        if ($result) {
            $parcelShop = new self();
            $parcelShop->id_sendy_cart_parcel_shop = $result['id_sendy_cart_parcel_shop'];
            $parcelShop->id_cart = $result['id_cart'];
            $parcelShop->id_reference = $result['id_reference'];
            $parcelShop->parcel_shop_id = $result['parcel_shop_id'];
            $parcelShop->parcel_shop_name = $result['parcel_shop_name'];
            $parcelShop->parcel_shop_address = $result['parcel_shop_address'];

            return $parcelShop;
        }

        return null;
    }
}
