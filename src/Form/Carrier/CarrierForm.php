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

namespace Sendy\PrestaShop\Form\Carrier;

use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Sendy\PrestaShop\Enum\Carrier;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CarrierForm extends TranslatorAwareType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'sendynl_parcel_shop_carrier',
                ChoiceType::class,
                [
                    'label' => $this->trans('Select a carrier', 'Modules.Sendynl.Admin', []),
                    'required' => true,
                    'choices' => Carrier::choices(),
                    'help' => $this->trans(
                        'This is used to determine which carrier to use when displaying parcelshops in the parcel shop picker.',
                        'Modules.Sendynl.Admin',
                    ),
                ]
            );
    }
}
