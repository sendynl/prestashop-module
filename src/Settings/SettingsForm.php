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

namespace Sendy\PrestaShop\Settings;

use PrestaShopBundle\Form\Admin\Type\SwitchType;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Sendy\PrestaShop\Enums\ProcessingMethod;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @see https://devdocs.prestashop-project.org/8/modules/creation/adding-configuration-page-modern/
 */
class SettingsForm extends TranslatorAwareType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $orderStatuses = [];
        $shops = [];

        $builder
            ->add('sendy_processing_method', ChoiceType::class, [
                'label' => $this->trans('Processing method', 'Modules.Sendy.Admin'),
                'choices' => [
                    'Sendy' => 'sendy',
                    'PrestaShop' => 'prestashop',
                ],
                'help' => $this->trans(
                    'You can choose to push orders to Sendy and process them in the Sendy app, or process them directly in PrestaShop.',
                    'Modules.Sendy.Admin'
                ),
            ])
            ->add('sendy_processable_status', ChoiceType::class, [
                'label' => $this->trans('Processable Order Status', 'Modules.Sendy.Admin'),
                'choices' => $orderStatuses,
                'required' => false,
                'attr' => ['data-conditional' => 'sendy_processing_method=' . ProcessingMethod::Sendy],
            ])
            ->add('sendy_default_shop', ChoiceType::class, [
                'label' => $this->trans('Default Sendy Shop', 'Modules.Sendy.Admin'),
                'choices' => $shops,
                'required' => false,
                'attr' => ['data-conditional' => 'sendy_processing_method=' . ProcessingMethod::Sendy],
            ])
            ->add('sendy_import_products', SwitchType::class, [
                'label' => $this->trans('Import products', 'Modules.Sendy.Admin'),
                'required' => false,
            ])
            ->add('sendy_import_weight', SwitchType::class, [
                'label' => $this->trans('Import weight', 'Modules.Sendy.Admin'),
                'required' => false,
            ])
            ->add('sendy_mark_order_as_completed', ChoiceType::class, [
                'label' => $this->trans('Mark order as completed', 'Modules.Sendy.Admin'),
                'choices' => [
                    'Manually' => 'manually',
                    'After shipment created' => 'after-shipment-created',
                    'After label printed' => 'after-label-printed',
                    'After shipment delivered' => 'after-shipment-delivered',
                ],
            ]);
    }
}
