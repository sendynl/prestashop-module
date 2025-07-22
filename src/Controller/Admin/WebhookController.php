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

use Context;
use Employee;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Sendy\PrestaShop\Action\HandleShipmentWebhook;
use Sendy\PrestaShop\Installer\SystemUser;
use Sendy\PrestaShop\Repository\ConfigurationRepository;
use Sendy\PrestaShop\Repository\ShipmentRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends FrameworkBundleAdminController
{
    private ShipmentRepository $shipmentRepository;
    private HandleShipmentWebhook $handleShipmentWebhook;
    private ConfigurationRepository $configurationRepository;

    public function __construct(
        ShipmentRepository $shipmentRepository,
        HandleShipmentWebhook $handleShipmentWebhook,
        ConfigurationRepository $configurationRepository
    ) {
        if (method_exists(parent::class, '__construct')) {
            parent::__construct();
        }
        $this->shipmentRepository = $shipmentRepository;
        $this->handleShipmentWebhook = $handleShipmentWebhook;
        $this->configurationRepository = $configurationRepository;
    }

    public function __invoke(Request $request): Response
    {
        $emptyResponse = new Response('', Response::HTTP_NO_CONTENT);
        $body = json_decode($request->getContent(), true);

        if (!isset($body['data']['event'], $body['data']['id'], $body['data']['resource'])) {
            return new JsonResponse(['message' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        if ($body['data']['resource'] !== 'shipment') {
            return $emptyResponse;
        }

        // Set a system user which is needed when PrestaShop creates stock movements
        SystemUser::ensureInstalled();
        Context::getContext()->employee = new Employee($this->configurationRepository->getSendySystemUserId());

        $shipment = $this->shipmentRepository->find($body['data']['id']);

        if ($shipment) {
            $this->handleShipmentWebhook->execute($shipment, $body['data']['event']);
        }

        return $emptyResponse;
    }
}
