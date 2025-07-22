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
use Sendy\PrestaShop\Enum\ProcessingMethod;
use Sendy\PrestaShop\Factory\ApiConnectionFactory;
use Sendy\PrestaShop\Repository\ConfigurationRepository;
use Sendy\PrestaShop\Repository\ShopConfigurationRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
        if ($this->shopConfigurationRepository->getProcessingMethod() !== ProcessingMethod::Sendy) {
            return;
        }

        $sendy = $this->apiConnectionFactory->buildConnectionUsingTokens();

        $currentWebhookId = $this->configurationRepository->getWebhookId();

        if ($currentWebhookId) {
            $webhookIds = array_map(fn ($webhook) => $webhook['id'], $sendy->webhook->list());

            if (in_array($currentWebhookId, $webhookIds, true)) {
                return;
            }
        }

        $webhook = $sendy->webhook->create([
            'url' => $this->router->generate('sendy_webhook', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'events' => [
                'shipment.generated',
                'shipment.deleted',
                'shipment.cancelled',
                'shipment.delivered',
            ],
        ]);

        $this->configurationRepository->setWebhookId($webhook['id']);
    }
}
