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

namespace Sendy\PrestaShop\Hook\Admin;

use Carrier;
use Sendy\PrestaShop\Repository\CarrierConfigRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Persists the configuration fields from {@see ActionCarrierFormBuilderModifier} after a carrier is created.
 * https://devdocs.prestashop-project.org/9/modules/concepts/hooks/list-of-hooks/actionaftercreateformnameformhandler/
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
    public function __invoke(array $params): void
    {
        $carrierId = $params['id'];
        $carrier = new \Carrier((int) $carrierId);

        if ($carrier->external_module_name !== 'sendynl') {
            return;
        }

        $this->carrierConfigRepository->saveSettings(
            $carrier->id_reference,
            true,
            $params['form_data']['sendynl_parcel_shop_carrier'] ?? ''
        );
    }
}
