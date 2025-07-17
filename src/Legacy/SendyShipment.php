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

class SendyShipment extends ObjectModel
{
    public $id_sendy_shipment;

    public $id_order;

    public static $definition = [
        'table' => 'sendy_shipment',
        'primary' => 'id_sendy_shipment',
        'fields' => [
            'id_sendy_shipment' => ['type' => self::TYPE_STRING, 'required' => true],
            'id_order' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
        ],
    ];
}
