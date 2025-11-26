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

use PrestaShop\PrestaShop\Core\Grid\Action\Bulk\Type\ButtonBulkAction;
use PrestaShop\PrestaShop\Core\Grid\Action\Bulk\Type\ModalFormSubmitBulkAction;
use PrestaShop\PrestaShop\Core\Grid\Action\Type\SubmitGridAction;
use PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface;
use PrestaShopBundle\Translation\TranslatorInterface;
use Sendy\PrestaShop\Grid\TrackAndTraceColumn;
use Sendy\PrestaShop\Repository\ConfigurationRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Modifies the order grid definition to add Sendy-specific actions and columns.
 *
 * @see https://devdocs.prestashop-project.org/9/modules/concepts/hooks/list-of-hooks/actionordergriddefinitionmodifier/
 */
final class ActionOrderGridDefinitionModifier
{
    private TranslatorInterface $translator;
    private ConfigurationRepository $configurationRepository;

    public function __construct(TranslatorInterface $translator, ConfigurationRepository $configurationRepository)
    {
        $this->translator = $translator;
        $this->configurationRepository = $configurationRepository;
    }

    /**
     * @param array{
     *     definition: GridDefinitionInterface,
     * } $params
     */
    public function __invoke(array $params): void
    {
        /** @var GridDefinitionInterface $definition */
        $definition = $params['definition'];

        $displayTrackAndTraceColumn = $this->configurationRepository->getDisplayTrackAndTraceColumn();

        // Grid bulk actions
        $definition->getBulkActions()
            ->add(
                (new ModalFormSubmitBulkAction('sendy_create_shipment'))
                    ->setName($this->translator->trans('Sendy - Create shipment', [], 'Modules.Sendynl.Admin'))
                    ->setOptions([
                        'submit_route' => 'sendy_orders_create_shipment',
                        'modal_id' => 'sendyCreateShipmentModal',
                    ])
            )
            ->add(
                (new ButtonBulkAction('sendy_print_label'))
                    ->setName($this->translator->trans('Sendy - Print label', [], 'Modules.Sendynl.Admin'))
                    ->setOptions([
                        'class' => 'sendy-print-label-bulk-action-submit-btn',
                    ])
            );

        // Grid columns
        if ($displayTrackAndTraceColumn) {
            $definition->getColumns()->addBefore(
                'actions',
                (new TrackAndTraceColumn('sendynl_track_and_trace'))
                    ->setName($this->translator->trans('Track and trace', [], 'Modules.Sendynl.Admin'))
            );
        }

        // Grid actions. Toggling the visibility only seems to work in PS 8.0.0 and later.
        if (version_compare(_PS_VERSION_, '8.0.0', '>=')) {
            $trackAndTraceColumnLabel = $displayTrackAndTraceColumn
                ? $this->translator->trans('Sendy - Hide track and trace column', [], 'Modules.Sendynl.Admin')
                : $this->translator->trans('Sendy - Show track and trace column', [], 'Modules.Sendynl.Admin');
            $trackAndTraceColumnIcon = $displayTrackAndTraceColumn ? 'visibility_off' : 'visibility';
            $definition->getGridActions()
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
