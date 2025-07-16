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

namespace Sendy\PrestaShop\Hook;

use PrestaShop\PrestaShop\Core\Grid\Action\Bulk\Type\ButtonBulkAction;
use PrestaShop\PrestaShop\Core\Grid\Action\Bulk\Type\ModalFormSubmitBulkAction;
use PrestaShop\PrestaShop\Core\Grid\Action\Type\SubmitGridAction;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface;
use PrestaShopBundle\Translation\TranslatorInterface;
use Sendy\PrestaShop\Grid\TrackAndTraceColumn;
use Sendy\PrestaShop\Repository\ConfigurationRepository;

final class ActionOrderGridDefinitionModifier
{
    private TranslatorInterface $translator;
    private ConfigurationRepository $configurationRepository;

    public function __construct(TranslatorInterface $translator, ConfigurationRepository $configurationRepository)
    {
        $this->translator = $translator;
        $this->configurationRepository = $configurationRepository;
    }

    public function __invoke(array $params)
    {
        /** @var GridDefinitionInterface $definition */
        $definition = $params['definition'];

        $displayShippingMethodColumn = $this->configurationRepository->getDisplayShippingMethodColumn();
        $displayTrackAndTraceColumn = $this->configurationRepository->getDisplayTrackAndTraceColumn();

        // Grid bulk actions
        $definition->getBulkActions()
            ->add(
                (new ModalFormSubmitBulkAction('sendy_create_shipment'))
                    ->setName($this->translator->trans('Sendy - Create shipment', [], 'Modules.Sendy.Admin'))
                    ->setOptions([
                        'submit_route' => 'sendy_orders_create_shipment',
                        'modal_id' => 'sendyCreateShipmentModal',
                    ])
            )
            ->add(
                (new ButtonBulkAction('sendy_print_label'))
                    ->setName($this->translator->trans('Sendy - Print label', [], 'Modules.Sendy.Admin'))
                    ->setOptions([
                        'class' => 'sendy-print-label-bulk-action-submit-btn',
                    ])
            );

        // Grid columns
        // if ($displayShippingMethodColumn) {
        //    // The sendy_shipping_method column is joined in the ActionOrderGridQueryBuilderModifier hook
        //    $definition->getColumns()->addBefore(
        //        'actions',
        //        (new DataColumn('sendy_shipping_method'))
        //            ->setName($this->translator->trans('Shipping method', [], 'Modules.Sendy.Admin'))
        //            ->setOptions([
        //                'field' => 'sendy_shipping_method',
        //            ]),
        //    );
        // }

        if ($displayTrackAndTraceColumn) {
            $definition->getColumns()->addBefore(
                'actions',
                (new TrackAndTraceColumn('sendy_track_and_trace'))
                    ->setName($this->translator->trans('Track and trace', [], 'Modules.Sendy.Admin'))
            );
        }

        // Grid actions. Toggling the visibility only seems to work in PS 8.0.0 and later.
        if (version_compare(_PS_VERSION_, '8.0.0', '>=')) {
            $shippingMethodColumnLabel = $displayShippingMethodColumn
                ? $this->translator->trans('Sendy - Hide shipping method column', [], 'Modules.Sendy.Admin')
                : $this->translator->trans('Sendy - Show shipping method column', [], 'Modules.Sendy.Admin');
            $shippingMethodColumnIcon = $displayShippingMethodColumn ? 'visibility_off' : 'visibility';
            $trackAndTraceColumnLabel = $displayTrackAndTraceColumn
                ? $this->translator->trans('Sendy - Hide track and trace column', [], 'Modules.Sendy.Admin')
                : $this->translator->trans('Sendy - Show track and trace column', [], 'Modules.Sendy.Admin');
            $trackAndTraceColumnIcon = $displayTrackAndTraceColumn ? 'visibility_off' : 'visibility';
            $definition->getGridActions()
                ->add(
                    (new SubmitGridAction('sendy_toggle_shipping_method_column'))
                        ->setName($shippingMethodColumnLabel)
                        ->setIcon($shippingMethodColumnIcon)
                        ->setOptions([
                            'submit_route' => 'sendy_orders_toggle_shipping_method_column',
                        ]),
                )
                ->add(
                    (new SubmitGridAction('sendy_toggle_track_and_trace_column'))
                        ->setName($trackAndTraceColumnLabel)
                        ->setIcon($trackAndTraceColumnIcon)
                        ->setOptions([
                            'submit_route' => 'sendy_orders_toggle_track_and_trace_column',
                        ]),
                );
        }
    }
}
