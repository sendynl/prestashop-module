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
use Sendy\PrestaShop\Factory\ApiConnectionFactory;
use Sendy\PrestaShop\Repository\ConfigurationRepository;

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
