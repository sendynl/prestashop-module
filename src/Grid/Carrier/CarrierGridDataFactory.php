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

namespace Sendy\PrestaShop\Grid\Carrier;

use Db;
use PrestaShop\PrestaShop\Core\Grid\Data\Factory\GridDataFactoryInterface;
use PrestaShop\PrestaShop\Core\Grid\Data\GridData;
use PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;

class CarrierGridDataFactory implements GridDataFactoryInterface
{
    public function getData(SearchCriteriaInterface $searchCriteria)
    {
        $prefix = _DB_PREFIX_;
        $query = <<<SQL
            SELECT c.id_carrier, c.name, scc.parcel_shop_carrier
            FROM {$prefix}carrier c
            JOIN {$prefix}sendy_carrier_config scc ON c.id_reference = scc.id_reference
            WHERE c.active = 1 AND c.external_module_name = 'sendy' AND c.deleted = 0
            ORDER BY c.name ASC
            SQL;
        $rows = Db::getInstance()->executeS($query);

        return new GridData(new RecordCollection($rows), count($rows), $query);
    }
}
