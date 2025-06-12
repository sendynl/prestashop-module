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

namespace Sendy\PrestaShop\Enums;

final class MarkOrderAsCompletedSetting extends StringBackedEnum
{
    public const Manually = 'manually';
    public const AfterShipmentCreated = 'after-shipment-created';
    public const AfterLabelPrinted = 'after-label-printed';
    public const AfterShipmentDelivered = 'after-shipment-delivered';
}
