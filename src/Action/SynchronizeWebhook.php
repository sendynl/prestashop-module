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

namespace Sendy\PrestaShop\Action;

use Sendy\Api\Exceptions\SendyException;
use Sendy\PrestaShop\Repository\ShopConfigurationRepository;

/**
 * Install or uninstall the webhook based on the current processing method.
 */
class SynchronizeWebhook
{
    private InstallWebhook $installWebhook;
    private UninstallWebhook $uninstallWebhook;
    private ShopConfigurationRepository $shopConfigurationRepository;

    public function __construct(
        InstallWebhook $installWebhook,
        UninstallWebhook $uninstallWebhook,
        ShopConfigurationRepository $shopConfigurationRepository
    ) {
        $this->installWebhook = $installWebhook;
        $this->uninstallWebhook = $uninstallWebhook;
        $this->shopConfigurationRepository = $shopConfigurationRepository;
    }

    /**
     * @throws SendyException
     */
    public function execute(): void
    {
        if ($this->shopConfigurationRepository->anyShopsUsingSendyProcessingMethod()) {
            // Install the webhook. This action will only proceed if it is not already installed.
            $this->installWebhook->execute();
        } else {
            // Uninstall the webhook if it still exists.
            $this->uninstallWebhook->execute();
        }
    }
}
