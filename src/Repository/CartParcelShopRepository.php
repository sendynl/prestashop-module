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

use Sendy\PrestaShop\Entity\SendyCartParcelShop;

class CartParcelShopRepository extends AbstractEntityRepository
{
    protected const ENTITY_CLASS = SendyCartParcelShop::class;
}
