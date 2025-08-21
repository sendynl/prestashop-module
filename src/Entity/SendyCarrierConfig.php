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

namespace Sendy\PrestaShop\Entity;

use Doctrine\ORM\Mapping as ORM;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @ORM\Entity
 *
 * @ORM\Table
 */
class SendyCarrierConfig
{
    /**
     * @ORM\Id
     *
     * @ORM\GeneratedValue
     *
     * @ORM\Column(name="id_sendy_carrier_config", type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(name="id_reference", type="integer")
     */
    private int $carrierReferenceId;

    /**
     * @ORM\Column(name="parcel_shop_delivery_enabled", type="boolean")
     */
    private bool $parcelShopDeliveryEnabled;

    /**
     * @ORM\Column(name="parcel_shop_carrier", type="string", length=255)
     */
    private ?string $parcelShopCarrier;

    public function getCarrierReferenceId(): int
    {
        return $this->carrierReferenceId;
    }

    public function setCarrierReferenceId(int $carrierReferenceId): void
    {
        $this->carrierReferenceId = $carrierReferenceId;
    }

    public function isParcelShopDeliveryEnabled(): bool
    {
        return $this->parcelShopDeliveryEnabled;
    }

    public function setParcelShopDeliveryEnabled(bool $parcelShopDeliveryEnabled): void
    {
        $this->parcelShopDeliveryEnabled = $parcelShopDeliveryEnabled;
    }

    public function getParcelShopCarrier(): ?string
    {
        return $this->parcelShopCarrier;
    }

    public function setParcelShopCarrier(?string $parcelShopCarrier): void
    {
        $this->parcelShopCarrier = $parcelShopCarrier;
    }
}
