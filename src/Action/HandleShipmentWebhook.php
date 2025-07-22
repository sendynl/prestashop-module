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

namespace Sendy\PrestaShop\Action;

use Order;
use PrestaShop\PrestaShop\Adapter\Shop\Context;
use Sendy\PrestaShop\Entity\SendyShipment;
use Sendy\PrestaShop\Enum\ProcessingMethod;
use Sendy\PrestaShop\Factory\ApiConnectionFactory;
use Sendy\PrestaShop\Repository\PackageRepository;
use Sendy\PrestaShop\Repository\ShipmentRepository;
use Sendy\PrestaShop\Repository\ShopConfigurationRepository;
use Sendy\PrestaShop\Support\Str;

class HandleShipmentWebhook
{
    private ShipmentRepository $shipmentRepository;
    private PackageRepository $packageRepository;
    private ShopConfigurationRepository $shopConfigurationRepository;
    private ApiConnectionFactory $apiConnectionFactory;
    private Context $shopContext;

    public function __construct(
        ShipmentRepository $shipmentRepository,
        PackageRepository $packageRepository,
        ShopConfigurationRepository $shopConfigurationRepository,
        ApiConnectionFactory $apiConnectionFactory,
        Context $shopContext
    ) {
        $this->shipmentRepository = $shipmentRepository;
        $this->packageRepository = $packageRepository;
        $this->shopConfigurationRepository = $shopConfigurationRepository;
        $this->apiConnectionFactory = $apiConnectionFactory;
        $this->shopContext = $shopContext;
    }

    public function execute(SendyShipment $shipment, string $event): void
    {
        $order = new Order($shipment->getOrderId());

        if (!$order->id) {
            $this->deleteShipment($shipment);

            return;
        }

        // Only handle events if the processing method is set to Sendy for this shop
        $this->shopContext->setShopContext($order->id_shop);
        $processingMethod = $this->shopConfigurationRepository->getProcessingMethod();

        if ($processingMethod !== ProcessingMethod::Sendy) {
            return;
        }

        if ($event === 'shipment.generated') {
            $this->handleGenerated($shipment, $order);
        } elseif ($event === 'shipment.deleted') {
            $this->deleteShipment($shipment);
        } elseif ($event === 'shipment.delivered') {
            $this->handleDelivered($shipment, $order);
        }
    }

    private function deleteShipment(SendyShipment $shipment)
    {
        $this->packageRepository->deleteByShipmentId($shipment->getId());
        $this->shipmentRepository->delete($shipment);
    }

    private function handleGenerated(SendyShipment $shipment, Order $order): void
    {
        $sendy = $this->apiConnectionFactory->buildConnectionUsingTokens();

        $shipmentData = $sendy->shipment->get($shipment->getId());

        foreach ($shipmentData['packages'] as $package) {
            $this->packageRepository->addPackageToShipment(
                $shipment->getId(),
                $package['uuid'] ?? Str::uuidv4(),
                $package['package_number'],
                $package['tracking_url']
            );
        }

        if ($status = $this->shopConfigurationRepository->getStatusGenerated()) {
            $order->setCurrentState($status);
        }
    }

    private function handleDelivered(SendyShipment $shipment, Order $order): void
    {
        if ($status = $this->shopConfigurationRepository->getStatusDelivered()) {
            $order->setCurrentState($status);
        }
    }
}
