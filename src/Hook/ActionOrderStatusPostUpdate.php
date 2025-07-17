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

use Db;
use Order;
use PrestaShopLogger;
use PrestaShopLoggerCore;
use Sendy\Api\Exceptions\SendyException;
use Sendy\PrestaShop\Action\CreateShipmentFromOrder;
use Sendy\PrestaShop\Enum\ProcessingMethod;
use Sendy\PrestaShop\Legacy\SendyShipment;
use Sendy\PrestaShop\Repository\ConfigurationRepository;

class ActionOrderStatusPostUpdate
{
    private CreateShipmentFromOrder $createShipmentFromOrder;
    private ConfigurationRepository $configurationRepository;

    public function __construct(
        CreateShipmentFromOrder $createShipmentFromOrder,
        ConfigurationRepository $configurationRepository,
    ) {
        $this->createShipmentFromOrder = $createShipmentFromOrder;
        $this->configurationRepository = $configurationRepository;
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

        // Only proceed if the processing method is Sendy
        if ($this->configurationRepository->getProcessingMethod() !== ProcessingMethod::Sendy) {
            return;
        }

        // Only proceed if the new order status is the processable status
        if ($params['newOrderStatus']->id !== $this->configurationRepository->getProcessableStatus()) {
            return;
        }

        // Only proceed if there is no shipment for this order yet.
        // Since this hook can be triggered from the front office, we use the legacy Db class instead of the repository.
        if (Db::getInstance()->getValue(
            'SELECT 1 FROM `' . _DB_PREFIX_ . 'sendy_shipment` WHERE `id_order` = ' . (int) $params['id_order']
        )) {
            return;
        }

        try {
            $order = new Order((int) $params['id_order']);
            $result = $this->createShipmentFromOrder->execute(
                $order,
                $this->configurationRepository->getDefaultShop(),
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
