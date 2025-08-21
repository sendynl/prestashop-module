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

namespace Sendy\PrestaShop\Action;

use Sendy\Api\Exceptions\SendyException;
use Sendy\PrestaShop\Factory\ApiConnectionFactory;
use Sendy\PrestaShop\Repository\ConfigurationRepository;
use Sendy\PrestaShop\Repository\ShopConfigurationRepository;
use Sendy\PrestaShop\Support\Arr;

if (!defined('_PS_VERSION_')) {
    exit;
}

class InstallWebhook
{
    private ConfigurationRepository $configurationRepository;
    private ShopConfigurationRepository $shopConfigurationRepository;
    private ApiConnectionFactory $apiConnectionFactory;

    public function __construct(
        ConfigurationRepository $configurationRepository,
        ShopConfigurationRepository $shopConfigurationRepository,
        ApiConnectionFactory $apiConnectionFactory
    ) {
        $this->configurationRepository = $configurationRepository;
        $this->shopConfigurationRepository = $shopConfigurationRepository;
        $this->apiConnectionFactory = $apiConnectionFactory;
    }

    /**
     * @throws SendyException
     */
    public function execute(): void
    {
        if (!$this->shopConfigurationRepository->anyShopsUsingSendyProcessingMethod()) {
            return;
        }

        $sendy = $this->apiConnectionFactory->buildConnectionUsingTokens();
        $currentWebhookId = $this->configurationRepository->getWebhookId();
        $url = \Context::getContext()->link->getModuleLink('sendy', 'webhook');

        $payload = [
            'url' => $url,
            'events' => [
                'shipment.generated',
                'shipment.deleted',
                'shipment.cancelled',
                'shipment.delivered',
            ],
        ];

        if ($currentWebhookId) {
            $webhook = Arr::find($sendy->webhook->list(), fn ($webhook) => $webhook['id'] === $currentWebhookId);

            if ($webhook) {
                if ($webhook['url'] !== $url) {
                    // If the webhook exists but the URL has changed, update it
                    $sendy->webhook->update($currentWebhookId, $payload);
                }

                return;
            }
        }

        // If the webhook does not exist, create it
        $webhook = $sendy->webhook->create($payload);

        $this->configurationRepository->setWebhookId($webhook['id']);
    }
}
