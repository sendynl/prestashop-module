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

namespace Sendy\PrestaShop\Actions;

use Sendy\Api\Exceptions\SendyException;
use Sendy\PrestaShop\Factories\ApiConnectionFactory;
use Sendy\PrestaShop\Repositories\ConfigurationRepository;

class UninstallWebhook
{
    private ConfigurationRepository $configurationRepository;
    private ApiConnectionFactory $apiConnectionFactory;

    public function __construct(
        ConfigurationRepository $configurationRepository,
        ApiConnectionFactory $apiConnectionFactory
    ) {
        $this->configurationRepository = $configurationRepository;
        $this->apiConnectionFactory = $apiConnectionFactory;
    }

    /**
     * @throws SendyException
     */
    public function execute(): void
    {
        $webhookId = $this->configurationRepository->getWebhookId();

        if ($webhookId) {
            $sendy = $this->apiConnectionFactory->buildConnectionUsingTokens();
            $sendy->webhook->delete($webhookId);
            $this->configurationRepository->setWebhookId(null);
        }
    }
}
