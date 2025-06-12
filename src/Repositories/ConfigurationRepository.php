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

namespace Sendy\PrestaShop\Repositories;

use InvalidArgumentException;
use PrestaShop\PrestaShop\Core\Domain\Configuration\ShopConfigurationInterface;
use Sendy\PrestaShop\Enums\ProcessingMethod;
use Sendy\PrestaShop\Support\Str;

/**
 * @todo multistore https://devdocs.prestashop-project.org/9/development/multistore/getting-started/
 *       authentication should be global, settings should be per shop
 */
class ConfigurationRepository
{
    private ShopConfigurationInterface $configuration;

    public function __construct(ShopConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return ProcessingMethod::*
     */
    public function getProcessingMethod(): string
    {
        return $this->configuration->get('SENDY_PROCESSING_METHOD', ProcessingMethod::PrestaShop);
    }

    public function setProcessingMethod(string $processingMethod): void
    {
        if (!in_array($processingMethod, ProcessingMethod::values(), true)) {
            throw new InvalidArgumentException(sprintf('Invalid processing method: %s', $processingMethod));
        }

        $this->configuration->set('SENDY_PROCESSING_METHOD', $processingMethod);
    }

    public function getProcessableStatus(): ?string
    {
        return $this->configuration->get('SENDY_PROCESSABLE_STATUS') ?: null;
    }

    public function setProcessableStatus(?string $processableStatus): void
    {
        $this->configuration->set('SENDY_PROCESSABLE_STATUS', $processableStatus);
    }

    /**
     * Get the UUID of the Sendy shop that should be used to create shipments.
     */
    public function getDefaultShop(): ?string
    {
        return $this->configuration->get('SENDY_DEFAULT_SHOP') ?: null;
    }

    public function setDefaultShop(?string $defaultShop): void
    {
        $this->configuration->set('SENDY_DEFAULT_SHOP', $defaultShop);
    }

    public function getImportProducts(): bool
    {
        return $this->configuration->getBoolean('SENDY_IMPORT_PRODUCTS', false);
    }

    public function setImportProducts(bool $importProducts): void
    {
        $this->configuration->set('SENDY_IMPORT_PRODUCTS', $importProducts);
    }

    public function getImportWeight(): bool
    {
        return $this->configuration->getBoolean('SENDY_IMPORT_WEIGHT', false);
    }

    public function setImportWeight(bool $importWeight): void
    {
        $this->configuration->set('SENDY_IMPORT_WEIGHT', $importWeight);
    }

    public function getMarkOrderAsCompleted(): bool
    {
        return $this->configuration->getBoolean('SENDY_MARK_ORDER_AS_COMPLETED', false);
    }

    public function setMarkOrderAsCompleted(bool $markOrderAsCompleted): void
    {
        $this->configuration->set('SENDY_MARK_ORDER_AS_COMPLETED', $markOrderAsCompleted);
    }

    public function ensureClientId(): string
    {
        $clientId = $this->configuration->get('SENDY_CLIENT_ID') ?: null;

        if ($clientId === null) {
            $clientId = Str::uuidv4();

            $this->configuration->set('SENDY_CLIENT_ID', $clientId);
        }

        return $clientId;
    }

    public function ensureClientSecret(): string
    {
        $clientSecret = $this->configuration->get('SENDY_CLIENT_SECRET') ?: null;

        if ($clientSecret === null) {
            $clientSecret = Str::random(40);

            $this->configuration->set('SENDY_CLIENT_SECRET', $clientSecret);
        }

        return $clientSecret;
    }

    public function getAccessToken(): ?string
    {
        return $this->configuration->get('SENDY_ACCESS_TOKEN') ?: null;
    }

    public function setAccessToken(string $accessToken): void
    {
        $this->configuration->set('SENDY_ACCESS_TOKEN', $accessToken);
    }

    public function getRefreshToken(): ?string
    {
        return $this->configuration->get('SENDY_REFRESH_TOKEN') ?: null;
    }

    public function setRefreshToken(string $refreshToken): void
    {
        $this->configuration->set('SENDY_REFRESH_TOKEN', $refreshToken);
    }

    public function getTokenExpires(): ?int
    {
        return $this->configuration->getInt('SENDY_TOKEN_EXPIRES') ?: null;
    }

    public function setTokenExpires(int $expires): void
    {
        $this->configuration->set('SENDY_TOKEN_EXPIRES', $expires);
    }

    public function pullOAuthState(): ?string
    {
        $state = $this->configuration->get('SENDY_OAUTH_STATE') ?: null;

        $this->configuration->set('SENDY_OAUTH_STATE', null);

        return $state;
    }

    public function setOAuthState(string $state): void
    {
        $this->configuration->set('SENDY_OAUTH_STATE', $state);
    }

    public function forgetAccessToken(): void
    {
        $this->configuration->set('SENDY_ACCESS_TOKEN', null);
        $this->configuration->set('SENDY_REFRESH_TOKEN', null);
        $this->configuration->set('SENDY_TOKEN_EXPIRES', null);
    }
}
