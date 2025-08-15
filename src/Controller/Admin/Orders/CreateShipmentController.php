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

namespace Sendy\PrestaShop\Controller\Admin\Orders;

use Order;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopLogger;
use Sendy\Api\Exceptions\SendyException;
use Sendy\PrestaShop\Action\CreateShipmentFromOrder;
use Sendy\PrestaShop\Exception\TokensMissingException;
use Sendy\PrestaShop\Repository\PackageRepository;
use Sendy\PrestaShop\Repository\ShipmentRepository;
use Sendy\PrestaShop\Support\Str;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateShipmentController extends FrameworkBundleAdminController
{
    private CreateShipmentFromOrder $createShipmentFromOrder;
    private ShipmentRepository $shipmentRepository;
    private PackageRepository $packageRepository;

    public function __construct(
        CreateShipmentFromOrder $createShipmentFromOrder,
        ShipmentRepository $shipmentRepository,
        PackageRepository $packageRepository
    ) {
        if (method_exists(parent::class, '__construct')) {
            parent::__construct();
        }

        $this->createShipmentFromOrder = $createShipmentFromOrder;
        $this->shipmentRepository = $shipmentRepository;
        $this->packageRepository = $packageRepository;
    }

    public function __invoke(Request $request): Response
    {
        PrestaShopLogger::addLog('Sendy - CreateShipmentController');

        $formData = $request->get('form');

        try {
            foreach ($formData['order_ids'] as $orderId) {
                $order = new Order((int) $orderId);

                if ($this->shipmentRepository->findShipmentByOrderId((int) $orderId)) {
                    $this->addFlash('warning', $this->trans('Shipment already exists for order %order%.', 'Modules.Sendy.Admin', [
                        '%order%' => $order->reference,
                    ]));

                    continue;
                }

                $result = $this->createShipmentFromOrder->execute(
                    $order,
                    $formData['shop_id'],
                    $formData['preference_id'],
                    (int) $formData['amount'] ?? 1
                );

                $this->shipmentRepository->addShipmentToOrder($order->id, $result['uuid']);

                if (isset($result['packages'])) {
                    foreach ($result['packages'] as $package) {
                        $this->packageRepository->addPackageToShipment(
                            $result['uuid'],
                            $package['uuid'] ?? Str::uuidv4(),
                            $package['package_number'],
                            $package['tracking_url']
                        );
                    }
                }
            }

            $this->addFlash(
                'success',
                $this->trans('Shipments created successfully.', 'Modules.Sendy.Admin')
            );
        } catch (SendyException|TokensMissingException $exception) {
            if (isset($order)) {
                $this->addFlash(
                    'error',
                    $this->trans('Error creating shipment for order %order%: %message%', 'Modules.Sendy.Admin', [
                        '%order%' => $order->reference,
                        '%message%' => $exception->getMessage(),
                    ]),
                );
            } else {
                $this->addFlash('error', $exception->getMessage());
            }
        }

        return new RedirectResponse(
            $request->headers->get('referer', $this->generateUrl('admin_orders_index'))
        );
    }
}
