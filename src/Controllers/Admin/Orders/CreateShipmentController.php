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

namespace Sendy\PrestaShop\Controllers\Admin\Orders;

use Order;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopLogger;
use Sendy\Api\Exceptions\SendyException;
use Sendy\PrestaShop\Actions\CreateShipmentFromOrder;
use Sendy\PrestaShop\Repositories\ShipmentRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateShipmentController extends FrameworkBundleAdminController
{
    private CreateShipmentFromOrder $createShipmentFromOrder;
    private ShipmentRepository $shipmentRepository;

    public function __construct(CreateShipmentFromOrder $createShipmentFromOrder, ShipmentRepository $shipmentRepository)
    {
        if (method_exists(parent::class, '__construct')) {
            parent::__construct();
        }

        $this->createShipmentFromOrder = $createShipmentFromOrder;
        $this->shipmentRepository = $shipmentRepository;
    }

    public function __invoke(Request $request): Response
    {
        PrestaShopLogger::addLog('Sendy - CreateShipmentController');

        try {
            foreach ($request->get('order_orders_bulk') as $orderId) {
                $order = new Order((int) $orderId);

                $result = $this->createShipmentFromOrder->execute(
                    $order,
                    '76e71dc0-a62f-11ec-9b8a-0050560104de', // TODO get from request
                    'c371fe4c-7c84-48dc-ae76-e39afcf5a3bc' // TODO get from request
                );

                $this->shipmentRepository->addShipmentToOrder($order->id, $result['uuid']);
            }

            $this->addFlash(
                'success',
                $this->trans('Shipments created successfully.', 'Modules.Sendy.Admin')
            );
        } catch (SendyException $exception) {
            if (isset($order)) {
                $this->addFlash(
                    'error',
                    "Error creating shipment for order {$order->reference}: " . $exception->getMessage()
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
