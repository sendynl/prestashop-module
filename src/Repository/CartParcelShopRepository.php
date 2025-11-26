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

use Sendy\PrestaShop\Entity\SendynlCartParcelShop;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @extends AbstractEntityRepository<SendynlCartParcelShop>
 */
class CartParcelShopRepository extends AbstractEntityRepository
{
    protected const ENTITY_CLASS = SendynlCartParcelShop::class;
}
