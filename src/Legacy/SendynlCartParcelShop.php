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

class SendynlCartParcelShop extends \ObjectModel
{
    public $id_cart;
    public $id_reference;
    public $parcel_shop_id;
    public $parcel_shop_name;
    public $parcel_shop_address;

    public static $definition = [
        'table' => 'sendynl_cart_parcel_shop',
        'primary' => 'id_sendynl_cart_parcel_shop',
        'fields' => [
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
     * @return SendynlCartParcelShop|null
     */
    public static function getByCartId(int $id_cart): ?SendynlCartParcelShop
    {
        $sql = 'SELECT id_sendynl_cart_parcel_shop FROM `' . _DB_PREFIX_ . 'sendynl_cart_parcel_shop` WHERE `id_cart` = ' . (int) $id_cart;
        $id = \Db::getInstance()->getValue($sql);

        if ($id) {
            $parcelShop = new self($id);

            return $parcelShop;
        }

        return null;
    }

    public static function getByCartIdAndCarrierReferenceId(int $id_cart, int $id_reference): ?SendynlCartParcelShop
    {
        $sql = 'SELECT id_sendynl_cart_parcel_shop FROM `' . _DB_PREFIX_ . 'sendynl_cart_parcel_shop` WHERE `id_cart` = ' . (int) $id_cart . ' AND `id_reference` = ' . (int) $id_reference;
        $id = \Db::getInstance()->getValue($sql);

        if ($id) {
            $parcelShop = new self($id);

            return $parcelShop;
        }

        return null;
    }

    public static function getForOrder(\Order $order): ?SendynlCartParcelShop
    {
        if (!$order->id_cart) {
            return null;
        }

        $carrier = new \Carrier((int) $order->id_carrier);

        return self::getByCartIdAndCarrierReferenceId((int) $order->id_cart, (int) $carrier->id_reference);
    }

    public static function getOrNewByCartId(int $id_cart): SendynlCartParcelShop
    {
        $instance = self::getByCartId($id_cart) ?? new self();

        $instance->id_cart = $id_cart;

        return $instance;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'id_cart' => $this->id_cart,
            'id_reference' => $this->id_reference,
            'parcel_shop_id' => $this->parcel_shop_id,
            'parcel_shop_name' => $this->parcel_shop_name,
            'parcel_shop_address' => $this->parcel_shop_address,
        ];
    }
}
