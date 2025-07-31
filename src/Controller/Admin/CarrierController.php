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

namespace Sendy\PrestaShop\Controller\Admin;

use Carrier;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Sendy\PrestaShop\Enum\Carrier as CarrierEnum;
use Sendy\PrestaShop\Repository\CarrierConfigRepository;
use Sendy\PrestaShop\Repository\CarrierRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CarrierController extends FrameworkBundleAdminController
{
    private CarrierRepository $carrierRepository;
    private CarrierConfigRepository $carrierConfigRepository;

    public function __construct(CarrierRepository $carrierRepository, CarrierConfigRepository $carrierConfigRepository)
    {
        $this->carrierRepository = $carrierRepository;
        $this->carrierConfigRepository = $carrierConfigRepository;
    }

    public function store(Request $request): Response
    {
        $carrierType = $request->get('carrier_form')['sendy_parcel_shop_carrier'];

        if (!in_array($carrierType, CarrierEnum::values())) {
            $this->addFlash('error', $this->trans('Invalid carrier.', 'Modules.Sendy.Admin'));

            return new RedirectResponse($this->generateUrl('sendy_settings'));
        }

        try {
            $id = $this->carrierRepository->create(
                $carrierType,
                $this->trans('Pick up at parcelshop', 'Modules.Sendy.Admin')
            );

            $this->carrierConfigRepository->saveSettings($id, true, $carrierType);
        } catch (Throwable $e) {
            $this->addFlash('error', $this->trans('Failed to create carrier.', 'Modules.Sendy.Admin'));

            return new RedirectResponse($this->generateUrl('sendy_settings'));
        }

        return new RedirectResponse($this->generateUrl('admin_carriers_edit', ['carrierId' => $id]));
    }

    public function updateParcelShopCarrier(Request $request, int $carrierId): Response
    {
        $carrier = new Carrier($carrierId);

        $this->carrierConfigRepository->saveSettings($carrier->id_reference, true, $request->get('value'));

        return new RedirectResponse($this->generateUrl('sendy_settings'));
    }
}
