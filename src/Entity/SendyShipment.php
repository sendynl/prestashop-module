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
class SendyShipment
{
    /**
     * @ORM\Id
     *
     * @ORM\Column(name="id_sendy_shipment", type="string", length=36, unique=true)
     */
    private string $id;

    /**
     * @ORM\Column(name="id_order", type="integer")
     */
    private int $orderId;

    public function __construct(string $id, int $orderId)
    {
        $this->id = $id;
        $this->orderId = $orderId;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }
}
