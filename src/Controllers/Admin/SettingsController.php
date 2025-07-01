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
use Sendy\PrestaShop\Forms\Settings\AuthenticateForm;
use Sendy\PrestaShop\Forms\Settings\SettingsFormHandler;
use Sendy\PrestaShop\Repositories\ConfigurationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SettingsController extends FrameworkBundleAdminController
{
    private ConfigurationRepository $configurationRepository;
    private SettingsFormHandler $settingsFormHandler;

    public function __construct(ConfigurationRepository $configurationRepository, SettingsFormHandler $settingsFormHandler)
    {
        if (method_exists(parent::class, '__construct')) {
            parent::__construct();
        }
        $this->configurationRepository = $configurationRepository;
        $this->settingsFormHandler = $settingsFormHandler;
    }

    public function index(Request $request): Response
    {
        $settingsForm = $this->settingsFormHandler->getForm();
        $settingsForm->handleRequest($request);

        if ($settingsForm->isSubmitted() && $settingsForm->isValid()) {
            $errors = $this->settingsFormHandler->save($settingsForm->getData());

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
