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

namespace Sendy\PrestaShop\Action;

use PrestaShop\PrestaShop\Adapter\Shop\Context;
use Sendy\Api\Exceptions\SendyException;
use Sendy\PrestaShop\Enum\ProcessingMethod;
use Sendy\PrestaShop\Factory\ApiConnectionFactory;
use Sendy\PrestaShop\Legacy\SendyPackage;
use Sendy\PrestaShop\Legacy\SendyShipment;
use Sendy\PrestaShop\Repository\ShopConfigurationRepository;
use Sendy\PrestaShop\Support\Str;

if (!defined('_PS_VERSION_')) {
    exit;
}

class HandleShipmentWebhook
{
    private ShopConfigurationRepository $shopConfigurationRepository;
    private ApiConnectionFactory $apiConnectionFactory;
    private Context $shopContext;

    public function __construct(
        ShopConfigurationRepository $shopConfigurationRepository,
        ApiConnectionFactory $apiConnectionFactory,
        Context $shopContext
    ) {
        $this->shopConfigurationRepository = $shopConfigurationRepository;
        $this->apiConnectionFactory = $apiConnectionFactory;
        $this->shopContext = $shopContext;
    }

    public function execute(SendyShipment $shipment, string $event): void
    {
        $order = new \Order((int) $shipment->id_order);

        if (!$order->id) {
            $this->deleteShipment($shipment);

            return;
        }

        // Only handle events if the processing method is set to Sendy for this shop
        $this->shopContext->setShopContext((int) $order->id_shop);
        $processingMethod = $this->shopConfigurationRepository->getProcessingMethod();

        if ($processingMethod !== ProcessingMethod::Sendy) {
            return;
        }

        $sendy = $this->apiConnectionFactory->buildConnectionUsingTokens();

        try {
            $shipmentData = $sendy->shipment->get($shipment->id_sendynl_shipment);
        } catch (SendyException $exception) {
            if ($exception->getCode() === 404) {
                $shipmentData = null;
            } else {
                throw $exception;
            }
        }

        if (!$this->verifyStatus($shipmentData, $event)) {
            return;
        }

        if ($event === 'shipment.generated') {
            $this->handleGenerated($shipment, $order, $shipmentData);
        } elseif ($event === 'shipment.deleted' || $event === 'shipment.cancelled') {
            $this->deleteShipment($shipment);
        } elseif ($event === 'shipment.delivered') {
            $this->handleDelivered($shipment, $order);
        }
    }

    /**
     * @param array<string, mixed> $shipmentData
     */
    private function handleGenerated(SendyShipment $shipment, \Order $order, array $shipmentData): void
    {
        foreach ($shipmentData['packages'] as $package) {
            SendyPackage::addPackageToShipment(
                $shipment->id_sendynl_shipment,
                $package['uuid'] ?? Str::uuidv4(),
                $package['package_number'],
                $package['tracking_url']
            );
        }

        if ($status = $this->shopConfigurationRepository->getStatusGenerated()) {
            $order->setCurrentState($status);
        }
    }

    private function deleteShipment(SendyShipment $shipment): void
    {
        SendyPackage::deleteByShipmentId($shipment->id_sendynl_shipment);
        SendyShipment::deleteByUuid($shipment->id_sendynl_shipment);
    }

    private function handleDelivered(SendyShipment $shipment, \Order $order): void
    {
        if ($status = $this->shopConfigurationRepository->getStatusDelivered()) {
            $order->setCurrentState($status);
        }
    }

    private function verifyStatus(?array $shipmentData, string $event): bool
    {
        return ($event === 'shipment.generated' && ($shipmentData['status'] ?? null) === 'generated')
            || ($event === 'shipment.delivered' && ($shipmentData['phase'] ?? null) === 'delivered')
            || ($event === 'shipment.deleted' && $shipmentData === null)
            || ($event === 'shipment.cancelled' && ($shipmentData['status'] ?? null) === 'cancelled');
    }
}
