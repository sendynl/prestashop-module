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

namespace Sendy\PrestaShop\Hook\Admin;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Modifies the order grid query builder to include Sendy shipment and package information.
 *
 * @see https://devdocs.prestashop-project.org/9/modules/concepts/hooks/list-of-hooks/actionordergridquerybuildermodifier/
 */
final class ActionOrderGridQueryBuilderModifier
{
    /**
     * @param array{
     *     search_query_builder: \Doctrine\DBAL\Query\QueryBuilder,
     *     count_query_builder: \Doctrine\DBAL\Query\QueryBuilder,
     *     search_criteria: \PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface,
     * } $params
     */
    public function __invoke(array $params): void
    {
        /** @var \Doctrine\DBAL\Query\QueryBuilder $queryBuilder */
        $queryBuilder = $params['search_query_builder'];

        $queryBuilder
            ->leftJoin(
                'o',
                _DB_PREFIX_ . 'sendy_shipment',
                'ss',
                'ss.id_order = o.id_order'
            )
            ->leftJoin(
                'ss',
                _DB_PREFIX_ . 'sendy_package',
                'sp',
                'sp.id_sendy_shipment = ss.id_sendy_shipment'
            )
            ->addSelect('ss.id_sendy_shipment AS sendy_shipment_id')
            ->addSelect('GROUP_CONCAT(sp.id_sendy_package) AS sendy_package_ids')
            ->addSelect('GROUP_CONCAT(sp.tracking_number) AS sendy_tracking_numbers')
            ->addGroupBy('o.id_order')
        ;
    }
}
