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

namespace Sendy\PrestaShop\Controller\Admin;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Sendy\Api\Exceptions\SendyException;
use Sendy\PrestaShop\Action\ApplyProcessingMethodChange;
use Sendy\PrestaShop\Form\Settings\AuthenticateForm;
use Sendy\PrestaShop\Form\Settings\SettingsFormHandler;
use Sendy\PrestaShop\Repository\ConfigurationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SettingsController extends FrameworkBundleAdminController
{
    private ConfigurationRepository $configurationRepository;
    private SettingsFormHandler $settingsFormHandler;
    private ApplyProcessingMethodChange $applyProcessingMethodChange;

    public function __construct(
        ConfigurationRepository $configurationRepository,
        SettingsFormHandler $settingsFormHandler,
        ApplyProcessingMethodChange $applyProcessingMethodChange
    ) {
        if (method_exists(parent::class, '__construct')) {
            parent::__construct();
        }
        $this->configurationRepository = $configurationRepository;
        $this->settingsFormHandler = $settingsFormHandler;
        $this->applyProcessingMethodChange = $applyProcessingMethodChange;
    }

    public function index(Request $request): Response
    {
        $settingsForm = $this->settingsFormHandler->getForm();
        $settingsForm->handleRequest($request);

        if ($settingsForm->isSubmitted() && $settingsForm->isValid()) {
            $processingMethodChanged = $this->configurationRepository->getProcessingMethod()
                !== $settingsForm->getData()['sendy_processing_method'];

            $errors = $this->settingsFormHandler->save($settingsForm->getData());

            if (empty($errors)) {
                if ($processingMethodChanged) {
                    try {
                        $this->applyProcessingMethodChange->execute($settingsForm->getData()['sendy_processing_method']);
                    } catch (SendyException $e) {
                        $this->addFlash('error', $this->trans(
                            'An error occurred while changing the processing method: %error%',
                            'Modules.Sendy.Admin',
                            ['%error%' => $e->getMessage()]
                        ));
                    }
                }

                $this->addFlash('success', $this->trans('Settings updated.', 'Admin.Notifications.Success'));

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
