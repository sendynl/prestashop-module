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

namespace Sendy\PrestaShop\Controllers\Admin;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Sendy\PrestaShop\Repositories\ConfigurationRepository;
use Sendy\PrestaShop\Settings\AuthenticateForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SettingsController extends FrameworkBundleAdminController
{
    private ConfigurationRepository $configurationRepository;

    public function __construct(ConfigurationRepository $configurationRepository)
    {
        if (method_exists(parent::class, '__construct')) {
            parent::__construct();
        }
        $this->configurationRepository = $configurationRepository;
    }

    public function index(Request $request): Response
    {
        /** @var \PrestaShop\PrestaShop\Core\Form\Handler $formHandler */
        $formHandler = $this->get('sendy.prestashop.settings.settings_form_handler');

        $settingsForm = $formHandler->getForm();
        $settingsForm->handleRequest($request);

        if ($settingsForm->isSubmitted() && $settingsForm->isValid()) {
            $errors = $formHandler->save($settingsForm->getData());

            if (empty($errors)) {
                $this->addFlash('success', $this->trans('Successful update.', 'Admin.Notifications.Success'));

                return $this->redirectToRoute('sendy_settings');
            }

            $this->flashErrors($errors);
        }

        return $this->render('@Modules/sendy/views/templates/admin/settings.html.twig', [
            'settingsFormView' => $settingsForm->createView(),
            'authenticateFormView' => $this->createForm(AuthenticateForm::class)->createView(),
            'shouldDisplaySettingsForm' => $this->configurationRepository->getAccessToken() !== null,
        ]);
    }
}
