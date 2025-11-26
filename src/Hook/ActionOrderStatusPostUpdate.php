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

namespace Sendy\PrestaShop\Hook;

use Order;
use Sendy\Api\Exceptions\SendyException;
use Sendy\PrestaShop\Action\CreateShipmentFromOrder;
use Sendy\PrestaShop\Enum\ProcessingMethod;
use Sendy\PrestaShop\Exception\TokensMissingException;
use Sendy\PrestaShop\Legacy\SendynlShipment;
use Sendy\PrestaShop\Repository\ShopConfigurationRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Creates a shipment in Sendy when an order reaches the processable status.
 *
 * This hook can be triggered from both the back office and front office.
 *
 * @see https://devdocs.prestashop-project.org/9/modules/concepts/hooks/list-of-hooks/actionorderstatuspostupdate/
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
    public function __invoke(array $params): void
    {
        $order = new \Order((int) $params['id_order']);

        // Only proceed if the processing method is Sendy
        if ($this->shopConfigurationRepository->getProcessingMethod((int) $order->id_shop) !== ProcessingMethod::Sendy) {
            return;
        }

        // Only proceed if the new order status is the processable status
        if ($params['newOrderStatus']->id !== $this->shopConfigurationRepository->getProcessableStatus((int) $order->id_shop)) {
            return;
        }

        // Only proceed if there is no shipment for this order yet.
        if (SendynlShipment::existsForOrderId((int) $params['id_order'])) {
            return;
        }

        try {
            $result = $this->createShipmentFromOrder->execute(
                $order,
                $this->shopConfigurationRepository->getDefaultShop((int) $order->id_shop),
                null
            );

            $shipment = new SendynlShipment();
            $shipment->id_sendynl_shipment = $result['uuid'];
            $shipment->id_order = (int) $params['id_order'];
            $shipment->save();
        } catch (SendyException|TokensMissingException $e) {
            \PrestaShopLogger::addLog(
                'Sendy - Error creating shipment: ' . $e->getMessage(),
                \PrestaShopLoggerCore::LOG_SEVERITY_LEVEL_ERROR,
                $e->getCode(),
                \Order::class,
                (int) $params['id_order']
            );
        }
    }
}
