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

namespace Sendy\PrestaShop\Form\Settings;

use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Sendy\Api\Exceptions\SendyException;
use Sendy\PrestaShop\Exception\TokensMissingException;
use Sendy\PrestaShop\Factory\ApiConnectionFactory;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

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

    /**
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        try {
            $sendy = $this->apiConnectionFactory->buildConnectionUsingTokens();
            $authenticatedAs = $sendy->me->get()['name'];
        } catch (SendyException|TokensMissingException $e) {
            // Assume the user is not authenticated
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
