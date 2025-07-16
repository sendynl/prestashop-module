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

namespace Sendy\PrestaShop\Repository;

use Sendy\PrestaShop\Entity\SendyShipment;

/**
 * @extends AbstractEntityRepository<SendyShipment>
 */
class ShipmentRepository extends AbstractEntityRepository
{
    protected const ENTITY_CLASS = SendyShipment::class;

    public function addShipmentToOrder(int $orderId, string $shipmentId): void
    {
        $this->save(new SendyShipment($shipmentId, $orderId));
    }

    public function findShipmentByOrderId(int $orderId): ?SendyShipment
    {
        return $this->repository->findOneBy(['orderId' => $orderId]);
    }
}
