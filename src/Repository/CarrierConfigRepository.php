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

use Sendy\PrestaShop\Entity\SendynlCarrierConfig;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @extends AbstractEntityRepository<SendynlCarrierConfig>
 */
class CarrierConfigRepository extends AbstractEntityRepository
{
    protected const ENTITY_CLASS = SendynlCarrierConfig::class;

    public function findByCarrierReferenceId(int $carrierReferenceId): ?SendynlCarrierConfig
    {
        return $this->repository->findOneBy(['carrierReferenceId' => $carrierReferenceId]);
    }

    public function saveSettings(
        int $carrierReferenceId,
        bool $parcelShopDeliveryEnabled,
        ?string $parcelShopCarrier
    ): void {
        $carrierConfig = $this->findByCarrierReferenceId($carrierReferenceId) ?? new SendynlCarrierConfig();
        $carrierConfig->setCarrierReferenceId($carrierReferenceId);
        $carrierConfig->setParcelShopDeliveryEnabled($parcelShopDeliveryEnabled);
        $carrierConfig->setParcelShopCarrier($parcelShopCarrier);

        $this->save($carrierConfig);
    }
}
