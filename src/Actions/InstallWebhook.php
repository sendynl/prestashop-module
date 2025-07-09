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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class InstallWebhook
{
    private ConfigurationRepository $configurationRepository;
    private UrlGeneratorInterface $router;
    private ApiConnectionFactory $apiConnectionFactory;

    public function __construct(
        ConfigurationRepository $configurationRepository,
        UrlGeneratorInterface $router,
        ApiConnectionFactory $apiConnectionFactory
    ) {
        $this->configurationRepository = $configurationRepository;
        $this->router = $router;
        $this->apiConnectionFactory = $apiConnectionFactory;
    }

    /**
     * @throws SendyException
     */
    public function execute(): void
    {
        $sendy = $this->apiConnectionFactory->buildConnectionUsingTokens();

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
