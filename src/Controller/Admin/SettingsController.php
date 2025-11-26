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

namespace Sendy\PrestaShop\Controller\Admin;

use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteria;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Sendy\Api\Exceptions\HttpException;
use Sendy\Api\Exceptions\SendyException;
use Sendy\PrestaShop\Action\SynchronizeCarriers;
use Sendy\PrestaShop\Action\SynchronizeWebhook;
use Sendy\PrestaShop\Exception\TokensMissingException;
use Sendy\PrestaShop\Form\Carrier\CarrierForm;
use Sendy\PrestaShop\Form\Settings\AuthenticateForm;
use Sendy\PrestaShop\Form\Settings\SettingsFormHandler;
use Sendy\PrestaShop\Grid\Carrier\CarrierGridFactory;
use Sendy\PrestaShop\Repository\ConfigurationRepository;
use Sendy\PrestaShop\Repository\ShopConfigurationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SettingsController extends FrameworkBundleAdminController
{
    private ConfigurationRepository $configurationRepository;
    private ShopConfigurationRepository $shopConfigurationRepository;
    private SettingsFormHandler $settingsFormHandler;
    private SynchronizeWebhook $synchronizeWebhook;
    private SynchronizeCarriers $synchronizeCarriers;
    private CarrierGridFactory $carrierGridFactory;

    public function __construct(
        ConfigurationRepository $configurationRepository,
        ShopConfigurationRepository $shopConfigurationRepository,
        SettingsFormHandler $settingsFormHandler,
        SynchronizeWebhook $synchronizeWebhook,
        SynchronizeCarriers $synchronizeCarriers,
        CarrierGridFactory $carrierGridFactory
    ) {
        if (method_exists(parent::class, '__construct')) {
            parent::__construct();
        }
        $this->configurationRepository = $configurationRepository;
        $this->shopConfigurationRepository = $shopConfigurationRepository;
        $this->settingsFormHandler = $settingsFormHandler;
        $this->synchronizeWebhook = $synchronizeWebhook;
        $this->synchronizeCarriers = $synchronizeCarriers;
        $this->carrierGridFactory = $carrierGridFactory;
    }

    public function index(Request $request): Response
    {
        $settingsForm = $this->settingsFormHandler->getForm();
        $settingsForm->handleRequest($request);

        if ($settingsForm->isSubmitted() && $settingsForm->isValid()) {
            $oldProcessingMethod = $this->shopConfigurationRepository->getProcessingMethod();
            $newProcessingMethod = $settingsForm->getData()['sendy_processing_method'];
            $processingMethodChanged = $oldProcessingMethod !== $newProcessingMethod;

            $errors = $this->settingsFormHandler->save($settingsForm->getData());

            if (empty($errors)) {
                if ($processingMethodChanged) {
                    try {
                        $this->synchronizeWebhook->execute();
                        $this->synchronizeCarriers->execute();
                    } catch (SendyException|TokensMissingException $e) {
                        $this->shopConfigurationRepository->setProcessingMethod($oldProcessingMethod);
                        $this->addFlash('error', $this->trans(
                            'An error occurred while changing the processing method: %error%',
                            'Modules.Sendynl.Admin',
                            ['%error%' => $e->getMessage()]
                        ));

                        if ($e instanceof HttpException) {
                            \PrestaShopLogger::addLog(
                                "Sendy - Processing method change failed: {$e->getMessage()} - {$e->getRequest()->getBody()}",
                                \PrestaShopLogger::LOG_SEVERITY_LEVEL_ERROR,
                                $e->getCode(),
                            );
                        }
                    }
                }

                $this->addFlash('success', $this->trans('Settings updated.', 'Admin.Notifications.Success'));

                return $this->redirectToRoute('sendynl_settings');
            }

            $this->flashErrors($errors);
        }

        $carrierGrid = $this->carrierGridFactory->getGrid(new SearchCriteria());

        return $this->render('@Modules/sendynl/views/templates/admin/settings.html.twig', [
            'settingsFormView' => $settingsForm->createView(),
            'authenticateFormView' => $this->createForm(AuthenticateForm::class)->createView(),
            'carrierFormView' => $this->createForm(CarrierForm::class)->createView(),
            'shouldDisplaySettingsForm' => $this->configurationRepository->getAccessToken() !== null,
            'carrierGrid' => $this->presentGrid($carrierGrid),
        ]);
    }
}
