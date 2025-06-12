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

use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class AuthenticateForm extends TranslatorAwareType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!($options['is_authenticated'] ?? false)) { // todo pass from controller
            $builder
                ->setAction('sendy_login') // TODO create route
                ->add('authenticate', SubmitType::class, [
                    'label' => $this->trans('Login with Sendy', 'Modules.Sendy.Admin'),
                    'attr' => [
                        'class' => 'btn btn-primary',
                    ],
                ]);
        } else {
            $builder
                ->setAction('sendy_logout') // TODO create route
                ->add('unauthenticate', SubmitType::class, [
                    'label' => $this->trans('Logout from Sendy', 'Modules.Sendy.Admin'),
                    'attr' => [
                        'class' => 'btn btn-outline-danger',
                    ],
                ]);
        }
    }
}
