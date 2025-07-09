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

namespace Sendy\PrestaShop\Repositories;

use Sendy\PrestaShop\Entity\SendyPackage;

/**
 * @extends AbstractEntityRepository<SendyPackage>
 */
class PackageRepository extends AbstractEntityRepository
{
    protected const ENTITY_CLASS = SendyPackage::class;

    /**
     * @return list<SendyPackage>
     */
    public function findPackagesByShipmentId(string $shipmentId): array
    {
        return $this->repository->findBy(['shipmentId' => $shipmentId]);
    }

    public function addPackageToShipment(
        string $shipmentId,
        string $packageId,
        string $packageNumber,
        string $trackingUrl
    ): void {
        $this->save(new SendyPackage($packageId, $shipmentId, $packageNumber, $trackingUrl));
    }

    public function deleteByShipmentId(string $shipmentId): void
    {
        $this->repository->createQueryBuilder('p')
            ->delete()
            ->where('p.shipmentId = :shipmentId')
            ->setParameter('shipmentId', $shipmentId)
            ->getQuery()
            ->execute();
    }
}
