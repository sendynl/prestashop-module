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
use Sendy\PrestaShop\Repository\ShopConfigurationRepository;
use Sendy\PrestaShop\Support\Arr;
use Sendy\PrestaShop\Support\Str;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tools;

class InstallWebhook
{
    private ConfigurationRepository $configurationRepository;
    private ShopConfigurationRepository $shopConfigurationRepository;
    private UrlGeneratorInterface $router;
    private ApiConnectionFactory $apiConnectionFactory;

    public function __construct(
        ConfigurationRepository $configurationRepository,
        ShopConfigurationRepository $shopConfigurationRepository,
        UrlGeneratorInterface $router,
        ApiConnectionFactory $apiConnectionFactory
    ) {
        $this->configurationRepository = $configurationRepository;
        $this->shopConfigurationRepository = $shopConfigurationRepository;
        $this->router = $router;
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
        $url = Str::withoutUrlQueryParameters(
            Tools::getShopDomainSsl(true) . $this->router->generate('sendy_webhook'),
            ['_token']
        );
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
