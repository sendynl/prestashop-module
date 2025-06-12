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

use LogicException;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Sendy\PrestaShop\Factories\ApiConnectionFactory;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AuthenticateForm extends TranslatorAwareType
{
    private UrlGeneratorInterface $router;
    private ApiConnectionFactory $apiConnectionFactory;

    public function __construct(
        \PrestaShopBundle\Translation\TranslatorInterface $translator,
        array $locales,
        UrlGeneratorInterface $router,
        ApiConnectionFactory $apiConnectionFactory
    ) {
        parent::__construct($translator, $locales);
        $this->router = $router;
        $this->apiConnectionFactory = $apiConnectionFactory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        try {
            $sendy = $this->apiConnectionFactory->buildConnectionUsingTokens();
            $authenticatedAs = $sendy->me->get()['name'];
        } catch (\Sendy\Api\Exceptions\SendyException $e) {
            // If the connection fails, we assume the user is not authenticated
            $authenticatedAs = null;
        } catch (LogicException $e) {
            // If the connection cannot be built, we assume the user is not authenticated
            $authenticatedAs = null;
        }

        if ($authenticatedAs) {
            $builder
                ->setAction($this->router->generate('sendy_logout'))
                ->add('unauthenticate', SubmitType::class, [
                    'label' => $this->trans('Logout %name%', 'Modules.Sendy.Admin', [
                        '%name%' => $authenticatedAs,
                    ]),
                    'attr' => [
                        'class' => 'btn btn-outline-danger',
                    ],
                ]);
        } else {
            $builder
                ->setAction($this->router->generate('sendy_login'))
                ->add('authenticate', SubmitType::class, [
                    'label' => $this->trans('Login with Sendy', 'Modules.Sendy.Admin'),
                    'attr' => [
                        'class' => 'btn btn-primary',
                    ],
                ]);
        }
    }
}
