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

if (!defined('_PS_VERSION_')) {
    exit;
}

class RegenerateWebhookSecret
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
        $clientId = $this->configurationRepository->ensureClientId();
        $response = $this->apiConnectionFactory->buildConnectionUsingTokens()
            ->post('/regenerate-webhook-secret/' . $clientId);

        $this->configurationRepository->setWebhookSecret($response['webhook_secret']);
    }
}
