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

use PrestaShop\PrestaShop\Core\Grid\Action\GridActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\LinkRowAction;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollectionInterface;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ActionColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\AbstractGridDefinitionFactory;

class CarrierGridDefinitionFactory extends AbstractGridDefinitionFactory
{
    protected function getId(): string
    {
        return 'sendy_carrier';
    }

    protected function getName(): string
    {
        return $this->trans('Carriers', [], 'Admin.Shipping.Feature');
    }

    protected function getColumns(): ColumnCollectionInterface
    {
        return (new ColumnCollection())
            ->add(
                (new DataColumn('id_carrier'))
                    ->setName($this->trans('ID', [], 'Admin.Global'))
                    ->setOptions(['field' => 'id_carrier'])
            )
            ->add(
                (new DataColumn('name'))
                    ->setName($this->trans('Name', [], 'Admin.Global'))
                    ->setOptions(['field' => 'name'])
            )
            ->add((new ActionColumn('actions'))
                ->setName($this->trans('Actions', [], 'Admin.Global'))
                ->setOptions([
                    'actions' => (new RowActionCollection())
                        ->add(
                            (new LinkRowAction('edit'))
                                ->setName($this->trans('Edit', [], 'Admin.Actions'))
                                ->setIcon('edit')
                                ->setOptions([
                                    'route' => 'admin_carriers_edit',
                                    'route_param_name' => 'carrierId',
                                    'route_param_field' => 'id_carrier',
                                    'clickable_row' => true,
                                ])
                        ),
                ])
            )

        ;
    }

    protected function getGridActions()
    {
        return new GridActionCollection()
            // ->add(new )
        ;
    }
}
