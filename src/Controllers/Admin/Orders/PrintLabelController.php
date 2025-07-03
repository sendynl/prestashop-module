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

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Sendy\PrestaShop\Factories\ApiConnectionFactory;
use Sendy\PrestaShop\Repositories\ShipmentRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PrintLabelController extends FrameworkBundleAdminController
{
    private ApiConnectionFactory $apiConnectionFactory;
    private ShipmentRepository $shipmentRepository;

    public function __construct(ApiConnectionFactory $apiConnectionFactory, ShipmentRepository $shipmentRepository)
    {
        $this->apiConnectionFactory = $apiConnectionFactory;
        $this->shipmentRepository = $shipmentRepository;
    }

    public function __invoke(Request $request): Response
    {
        $shipmentIds = [];
        foreach ($request->get('order_orders_bulk', []) as $orderId) {
            $shipment = $this->shipmentRepository->findShipmentByOrderId((int) $orderId);
            if ($shipment) {
                $shipmentIds[] = $shipment->getId();
            }
        }

        if ($shipmentIds === []) {
            return new JsonResponse(
                ['message' => $this->trans('No shipments found for the selected orders.', 'Modules.Sendy.Admin')],
                400,
            );
        }

        try {
            $sendy = $this->apiConnectionFactory->buildConnectionUsingTokens();
            $responseBody = $sendy->label->get($shipmentIds);
        } catch (\Sendy\Api\Exceptions\SendyException $e) {
            return new JsonResponse(
                [
                    'message' => $this->trans('Error while fetching labels: %error%', [
                        '%error%' => $e->getMessage(),
                    ], 'Modules.Sendy.Admin'),
                ],
                502,
            );
        }

        return new JsonResponse($responseBody, 200, $sendy->sendyHeaders);
    }
}
