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

final class ActionCarrierFormDataProviderData
{
    private CarrierConfigRepository $carrierConfigRepository;

    public function __construct(CarrierConfigRepository $carrierConfigRepository)
    {
        $this->carrierConfigRepository = $carrierConfigRepository;
    }

    /**
     * @param array{
     *     data: array<string, mixed>,
     *     id: ?int,
     *     options: array<string, mixed>
     * } $params
     */
    public function __invoke(array &$params): void
    {
        if ($params['id'] === null) {
            return;
        }

        $carrier = new Carrier($params['id']);
        $carrierConfig = $this->carrierConfigRepository->findByCarrierReferenceId($carrier->id_reference);

        if ($carrierConfig === null) {
            return;
        }

        $params['data']['sendy']['sendy_parcel_shop_carrier'] = $carrierConfig->getParcelShopCarrier();
    }
}
