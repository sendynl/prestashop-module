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

use Sendy\PrestaShop\Entity\SendynlPackage;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @extends AbstractEntityRepository<SendynlPackage>
 */
class PackageRepository extends AbstractEntityRepository
{
    protected static function getEntityClass(): string
    {
        return SendynlPackage::class;
    }

    /**
     * @return list<SendynlPackage>
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
        $existingPackage = $this->find($packageId);

        if ($existingPackage) {
            $existingPackage->setShipmentId($shipmentId);
            $existingPackage->setTrackingNumber($packageNumber);
            $existingPackage->setTrackingUrl($trackingUrl);
            $this->save($existingPackage);
        } else {
            $this->save(new SendynlPackage($packageId, $shipmentId, $packageNumber, $trackingUrl));
        }
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
