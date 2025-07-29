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

namespace Sendy\PrestaShop\Hook\Admin;

use Carrier;
use Sendy\PrestaShop\Repository\CarrierConfigRepository;

/**
 * Persists the configuration fields from {@see ActionCarrierFormBuilderModifier} after a carrier is created.
 */
final class ActionAfterCreateCarrierFormHandler
{
    private CarrierConfigRepository $carrierConfigRepository;

    public function __construct(CarrierConfigRepository $carrierConfigRepository)
    {
        $this->carrierConfigRepository = $carrierConfigRepository;
    }

    /**
     * @param array{
     *     id: int,
     *     form_data: array<string, mixed>,
     * } $params
     */
    public function __invoke(array $params)
    {
        $carrierId = $params['id'];
        $carrier = new Carrier((int) $carrierId);

        $this->carrierConfigRepository->saveSettings(
            $carrier->id_reference,
            $params['form_data']['sendy']['sendy_parcel_shop_delivery_enabled'] ?? false,
            $params['form_data']['sendy']['sendy_parcel_shop_carrier'] ?? null
        );
    }
}
