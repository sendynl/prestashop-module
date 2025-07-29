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

namespace Sendy\PrestaShop\Hook;

use Order;
use PrestaShopLogger;
use PrestaShopLoggerCore;
use Sendy\Api\Exceptions\SendyException;
use Sendy\PrestaShop\Action\CreateShipmentFromOrder;
use Sendy\PrestaShop\Enum\ProcessingMethod;
use Sendy\PrestaShop\Legacy\SendyShipment;
use Sendy\PrestaShop\Repository\ShopConfigurationRepository;

/**
 * Creates a shipment in Sendy when an order reaches the processable status.
 *
 * This hook can be triggered from both the back office and front office.
 */
final class ActionOrderStatusPostUpdate
{
    private CreateShipmentFromOrder $createShipmentFromOrder;
    private ShopConfigurationRepository $shopConfigurationRepository;

    public function __construct(
        CreateShipmentFromOrder $createShipmentFromOrder,
        ShopConfigurationRepository $shopConfigurationRepository
    ) {
        $this->createShipmentFromOrder = $createShipmentFromOrder;
        $this->shopConfigurationRepository = $shopConfigurationRepository;
    }

    /**
     * @param array{
     *     newOrderStatus: \OrderState,
     *     oldOrderStatus: \OrderState,
     *     id_order: int,
     * } $params
     */
    public function __invoke(array $params)
    {
        PrestaShopLogger::addLog('Sendy - ActionOrderStatusPostUpdate hook class - ' . print_r($params, true));

        $order = new Order((int) $params['id_order']);

        // Only proceed if the processing method is Sendy
        if ($this->shopConfigurationRepository->getProcessingMethod($order->id_shop) !== ProcessingMethod::Sendy) {
            return;
        }

        // Only proceed if the new order status is the processable status
        if ($params['newOrderStatus']->id !== $this->shopConfigurationRepository->getProcessableStatus($order->id_shop)) {
            return;
        }

        // Only proceed if there is no shipment for this order yet.
        if (SendyShipment::existsForOrderId((int) $params['id_order'])) {
            return;
        }

        try {
            $result = $this->createShipmentFromOrder->execute(
                $order,
                $this->shopConfigurationRepository->getDefaultShop($order->id_shop),
                null
            );

            $shipment = new SendyShipment();
            $shipment->id_sendy_shipment = $result['uuid'];
            $shipment->id_order = (int) $params['id_order'];
            $shipment->save();
        } catch (SendyException $e) {
            PrestaShopLogger::addLog(
                'Sendy - Error creating shipment: ' . $e->getMessage(),
                PrestaShopLoggerCore::LOG_SEVERITY_LEVEL_ERROR,
                $e->getCode(),
                Order::class,
                (int) $params['id_order']
            );
        }
    }
}
