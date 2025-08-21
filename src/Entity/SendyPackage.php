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
class SendyPackage
{
    /**
     * @ORM\Id
     *
     * @ORM\Column(name="id_sendy_package", type="string", length=36, unique=true)
     */
    private string $id;

    /**
     * @ORM\Column(name="id_sendy_shipment", type="string", length=36)
     */
    private string $shipmentId;

    /**
     * @ORM\Column(name="tracking_number", type="string", length=255)
     */
    private string $trackingNumber;

    /**
     * @ORM\Column(name="tracking_url", type="string", length=255)
     */
    private string $trackingUrl;

    public function __construct(string $id, string $shipmentId, string $trackingNumber, string $trackingUrl)
    {
        $this->id = $id;
        $this->shipmentId = $shipmentId;
        $this->trackingNumber = $trackingNumber;
        $this->trackingUrl = $trackingUrl;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getShipmentId(): string
    {
        return $this->shipmentId;
    }

    public function getTrackingNumber(): string
    {
        return $this->trackingNumber;
    }

    public function getTrackingUrl(): string
    {
        return $this->trackingUrl;
    }

    public function setShipmentId(string $shipmentId): void
    {
        $this->shipmentId = $shipmentId;
    }

    public function setTrackingNumber(string $trackingNumber): void
    {
        $this->trackingNumber = $trackingNumber;
    }

    public function setTrackingUrl(string $trackingUrl): void
    {
        $this->trackingUrl = $trackingUrl;
    }
}
