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

use PrestaShop\PrestaShop\Core\Grid\Data\Factory\GridDataFactoryInterface;
use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\GridDefinitionFactoryInterface;
use PrestaShop\PrestaShop\Core\Grid\Filter\GridFilterFormFactoryInterface;
use PrestaShop\PrestaShop\Core\Grid\GridFactory;
use PrestaShop\PrestaShop\Core\Grid\GridFactoryInterface;
use PrestaShop\PrestaShop\Core\Grid\GridInterface;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;
use PrestaShop\PrestaShop\Core\Hook\HookDispatcherInterface;

class CarrierGridFactory implements GridFactoryInterface
{
    private GridFactory $gridFactory;

    public function __construct(
        GridDefinitionFactoryInterface $definitionFactory,
        GridDataFactoryInterface $dataFactory,
        GridFilterFormFactoryInterface $filterFormFactory,
        ?HookDispatcherInterface $hookDispatcher = null
    ) {
        $this->gridFactory = new GridFactory(
            $definitionFactory,
            $dataFactory,
            $filterFormFactory,
            $hookDispatcher,
        );
    }

    public function getGrid(SearchCriteriaInterface $searchCriteria): GridInterface
    {
        return $this->gridFactory->getGrid($searchCriteria);
    }
}
