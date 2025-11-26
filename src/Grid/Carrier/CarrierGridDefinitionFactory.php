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

namespace Sendy\PrestaShop\Grid\Carrier;

use PrestaShop\PrestaShop\Core\Grid\Action\GridActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\LinkRowAction;
use PrestaShop\PrestaShop\Core\Grid\Action\Type\LinkGridAction;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollectionInterface;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ActionColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ChoiceColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\AbstractGridDefinitionFactory;
use PrestaShopBundle\Event\Dispatcher\NullDispatcher;
use PrestaShopBundle\Translation\TranslatorInterface;
use Sendy\PrestaShop\Form\Carrier\CarrierChoiceProvider;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CarrierGridDefinitionFactory extends AbstractGridDefinitionFactory
{
    private CarrierChoiceProvider $carrierChoiceProvider;

    public function __construct(
        TranslatorInterface $translator,
        CarrierChoiceProvider $carrierChoiceProvider
    ) {
        parent::__construct(new NullDispatcher());
        $this->setTranslator($translator);
        $this->carrierChoiceProvider = $carrierChoiceProvider;
    }

    protected function getId(): string
    {
        return 'sendynl_carrier';
    }

    protected function getName(): string
    {
        return $this->trans('Parcelshop carriers', [], 'Modules.Sendynl.Admin');
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
            ->add(
                (new ChoiceColumn('carrier'))
                    ->setName($this->trans('Carrier', [], 'Admin.Shipping.Feature'))
                    ->setOptions([
                        'field' => 'parcel_shop_carrier',
                        'choice_provider' => $this->carrierChoiceProvider,
                        'route' => 'sendynl_carriers_update',
                        'record_route_params' => [
                            'id_carrier' => 'carrierId',
                        ],
                    ])
            )
            ->add(
                (new ActionColumn('actions'))
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
            );
    }

    protected function getGridActions()
    {
        return (new GridActionCollection())
            ->add(
                (new LinkGridAction('create_carrier'))
                    ->setOptions(['route' => 'admin_carriers_create'])
                    ->setName($this->trans('Add new carrier', [], 'Admin.Shipping.Feature'))
            );
    }
}
