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

namespace Sendy\PrestaShop\Actions;

use Order;
use Sendy\PrestaShop\Entity\SendyShipment;
use Sendy\PrestaShop\Factories\ApiConnectionFactory;
use Sendy\PrestaShop\Repositories\ConfigurationRepository;
use Sendy\PrestaShop\Repositories\PackageRepository;
use Sendy\PrestaShop\Repositories\ShipmentRepository;
use Sendy\PrestaShop\Support\Str;

class HandleShipmentWebhook
{
    private ShipmentRepository $shipmentRepository;
    private PackageRepository $packageRepository;
    private ConfigurationRepository $configurationRepository;
    private ApiConnectionFactory $apiConnectionFactory;

    public function __construct(
        ShipmentRepository $shipmentRepository,
        PackageRepository $packageRepository,
        ConfigurationRepository $configurationRepository,
        ApiConnectionFactory $apiConnectionFactory
    ) {
        $this->shipmentRepository = $shipmentRepository;
        $this->packageRepository = $packageRepository;
        $this->configurationRepository = $configurationRepository;
        $this->apiConnectionFactory = $apiConnectionFactory;
    }

    public function execute(SendyShipment $shipment, string $event): void
    {
        $order = new Order($shipment->getOrderId());

        if (!$order->id) {
            $this->deleteShipment($shipment);

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

        if ($status = $this->configurationRepository->getStatusGenerated()) {
            $order->setCurrentState($status);
        }
    }

    private function handleDelivered(SendyShipment $shipment, Order $order): void
    {
        if ($status = $this->configurationRepository->getStatusDelivered()) {
            $order->setCurrentState($status);
        }
    }
}
