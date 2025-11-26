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
class SendyCartParcelShop
{
    /**
     * @ORM\Id
     *
     * @ORM\Column(type="integer", name="id_sendynl_cart_parcel_shop")
     *
     * @ORM\GeneratedValue
     */
    private int $id;

    /**
     * @ORM\Column(type="integer", name="id_cart")
     */
    private int $cartId;

    /**
     * The reference ID of the carrier.
     *
     * @ORM\Column(type="integer", name="id_reference")
     */
    private int $referenceId;

    /**
     * The external ID of the parcel shop.
     *
     * @ORM\Column(type="string", name="parcel_shop_id")
     */
    private string $parcelShopId;

    /**
     * @ORM\Column(type="string", name="parcel_shop_name")
     */
    private string $parcelShopName;

    /**
     * @ORM\Column(type="text", name="parcel_shop_address")
     */
    private string $parcelShopAddress;

    public function __construct(
        int $cartId,
        int $referenceId,
        string $parcelShopId,
        string $parcelShopName,
        string $parcelShopAddress
    ) {
        $this->cartId = $cartId;
        $this->referenceId = $referenceId;
        $this->parcelShopId = $parcelShopId;
        $this->parcelShopName = $parcelShopName;
        $this->parcelShopAddress = $parcelShopAddress;
    }
}
