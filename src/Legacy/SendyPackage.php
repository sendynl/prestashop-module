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

class SendyPackage extends \ObjectModel
{
    public $id_sendy_package;
    public $id_sendy_shipment;
    public $tracking_number;
    public $tracking_url;

    public static $definition = [
        'table' => 'sendy_package',
        'primary' => 'id_sendy_package',
        'fields' => [
            'id_sendy_package' => ['type' => self::TYPE_STRING, 'required' => true],
            'id_sendy_shipment' => ['type' => self::TYPE_STRING, 'required' => true],
            'tracking_number' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true],
            'tracking_url' => ['type' => self::TYPE_STRING, 'validate' => 'isUrl', 'required' => false],
        ],
    ];

    public static function getByUuid(string $uuid): ?self
    {
        $package = new self($uuid);

        if (is_null($package->id_sendy_package)) {
            return null;
        }

        return $package;
    }

    public static function uuidExists(string $uuid): bool
    {
        return (bool) \Db::getInstance()->getValue(
            'select 1 from `' . _DB_PREFIX_ . "sendy_package` where `id_sendy_package` = '" . pSQL($uuid) . "'"
        );
    }

    /**
     * Add or update a package in a shipment.
     *
     * Bypasses the ORM to directly interact with the database, as the ORM does not support string primary keys.
     */
    public static function addPackageToShipment(
        string $shipmentId,
        string $packageId,
        string $packageNumber,
        string $trackingUrl
    ): void {
        if (self::uuidExists($packageId)) {
            \Db::getInstance()->update('sendy_package', [
                'id_sendy_shipment' => pSQL($shipmentId),
                'tracking_number' => pSQL($packageNumber),
                'tracking_url' => pSQL($trackingUrl),
            ], '`id_sendy_package` = \'' . pSQL($packageId) . '\'');
        } else {
            \Db::getInstance()->insert('sendy_package', [
                'id_sendy_package' => pSQL($packageId),
                'id_sendy_shipment' => pSQL($shipmentId),
                'tracking_number' => pSQL($packageNumber),
                'tracking_url' => pSQL($trackingUrl),
            ]);
        }
    }

    /**
     * Delete all packages associated with a shipment.
     */
    public static function deleteByShipmentId(string $shipmentId): void
    {
        \Db::getInstance()->delete('sendy_package', '`id_sendy_shipment` = \'' . pSQL($shipmentId) . '\'');
    }
}
