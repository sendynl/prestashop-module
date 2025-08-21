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

namespace Sendy\PrestaShop\Controller\Admin\Orders;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Sendy\Api\Exceptions\SendyException;
use Sendy\PrestaShop\Exception\TokensMissingException;
use Sendy\PrestaShop\Factory\ApiConnectionFactory;
use Sendy\PrestaShop\Repository\ShipmentRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

if (!defined('_PS_VERSION_')) {
    exit;
}

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
        foreach ($request->get('order_ids', []) as $orderId) {
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
        } catch (SendyException|TokensMissingException $e) {
            return new JsonResponse(
                [
                    'message' => $this->trans('Error while fetching labels: %error%', 'Modules.Sendy.Admin', [
                        '%error%' => $e->getMessage(),
                    ]),
                ],
                502,
            );
        }

        return new JsonResponse($responseBody, 200, $sendy->sendyHeaders);
    }
}
