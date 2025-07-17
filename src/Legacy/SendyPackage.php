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

use ObjectModel;

class SendyPackage extends ObjectModel
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
}
