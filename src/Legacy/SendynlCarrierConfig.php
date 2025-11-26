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

class SendynlCarrierConfig extends \ObjectModel
{
    public $id_sendynl_carrier_config;
    public $id_reference;
    public $parcel_shop_delivery_enabled;
    public $parcel_shop_carrier;

    public static $definition = [
        'table' => 'sendynl_carrier_config',
        'primary' => 'id_sendynl_carrier_config',
        'fields' => [
            'id_sendynl_carrier_config' => ['type' => self::TYPE_INT, 'auto_increment' => true],
            'id_reference' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'parcel_shop_delivery_enabled' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true, 'default' => 0],
            'parcel_shop_carrier' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255],
        ],
    ];

    /**
     * Get the carrier configuration by reference ID.
     *
     * @param int $id_reference
     *
     * @return SendynlCarrierConfig|null
     */
    public static function getByReferenceId(int $id_reference): ?SendynlCarrierConfig
    {
        $prefix = _DB_PREFIX_;
        $sql = "SELECT * FROM `{$prefix}sendynl_carrier_config` WHERE `id_reference` = {$id_reference}";
        $result = \Db::getInstance()->getRow($sql);

        if ($result) {
            $config = new self();
            $config->id_sendynl_carrier_config = $result['id_sendynl_carrier_config'];
            $config->id_reference = $result['id_reference'];
            $config->parcel_shop_delivery_enabled = (bool) $result['parcel_shop_delivery_enabled'];
            $config->parcel_shop_carrier = $result['parcel_shop_carrier'];

            return $config;
        }

        return null;
    }

    public function carrierRequiresParcelShop(): bool
    {
        return $this->parcel_shop_delivery_enabled && !empty($this->parcel_shop_carrier);
    }
}
