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

namespace Sendy\PrestaShop\Hook\Admin;

use PrestaShopBundle\Form\Admin\Type\SwitchType;
use Sendy\PrestaShop\Enum\Carrier;
use Symfony\Component\Form\Extension\Core\Type\FormType;

/**
 * Modifies the carrier form to add a 'Sendy' tab with configuration fields.
 */
final class ActionCarrierFormBuilderModifier
{
    /**
     * @param array{
     *     form_builder: \Symfony\Component\Form\FormBuilderInterface,
     *     data: array<string, mixed>,
     *     options: array<string, mixed>,
     *     id: ?int,
     * } $params
     */
    public function __invoke($params)
    {
        // Add a 'Sendy' tab to the carrier form
        $params['form_builder']->add(
            'sendy',
            FormType::class,
            [
                'label' => 'Sendy',
                'required' => false,
            ]
        );

        // Add fields to the 'Sendy' tab
        $params['form_builder']->get('sendy')
            ->add(
                'sendy_parcel_shop_delivery_enabled',
                SwitchType::class,
                [
                    'label' => 'Show parcel shop picker',
                    'required' => false,
                    'help' => 'Whether this carrier should display a parcel shop picker in the checkout.',
                ]
            )
            ->add(
                'sendy_parcel_shop_carrier',
                \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class,
                [
                    'label' => 'Select a carrier',
                    'required' => false,
                    'choices' => Carrier::choices(),
                    'help' => 'The carrier used to display parcel shops in the parcel shop picker.',
                ]
            );
    }
}
