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

namespace Sendy\PrestaShop\Repository;

use Sendy\PrestaShop\Entity\SendynlShipment;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @extends AbstractEntityRepository<SendynlShipment>
 */
class ShipmentRepository extends AbstractEntityRepository
{
    protected static function getEntityClass(): string
    {
        return SendynlShipment::class;
    }

    public function addShipmentToOrder(int $orderId, string $shipmentId): void
    {
        $this->save(new SendynlShipment($shipmentId, $orderId));
    }

    public function findShipmentByOrderId(int $orderId): ?SendynlShipment
    {
        return $this->repository->findOneBy(['orderId' => $orderId]);
    }
}
