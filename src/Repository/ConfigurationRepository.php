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

namespace Sendy\PrestaShop\Repository;

use PrestaShop\PrestaShop\Core\Domain\Configuration\ShopConfigurationInterface;
use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint;
use Sendy\PrestaShop\Support\Str;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * This repository handles global configuration like OAuth data and UI preferences.
 */
class ConfigurationRepository
{
    private ShopConfigurationInterface $configuration;

    public function __construct(ShopConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    public function ensureClientId(): string
    {
        $clientId = $this->configuration->get('SENDY_CLIENT_ID') ?: null;

        if ($clientId === null) {
            $clientId = Str::uuidv4();

            $this->configuration->set('SENDY_CLIENT_ID', $clientId, ShopConstraint::allShops());
        }

        return $clientId;
    }

    public function ensureClientSecret(): string
    {
        $clientSecret = $this->configuration->get('SENDY_CLIENT_SECRET') ?: null;

        if ($clientSecret === null) {
            $clientSecret = Str::random(40);

            $this->configuration->set('SENDY_CLIENT_SECRET', $clientSecret, ShopConstraint::allShops());
        }

        return $clientSecret;
    }

    public function getAccessToken(): ?string
    {
        return $this->configuration->get('SENDY_ACCESS_TOKEN') ?: null;
    }

    public function setAccessToken(string $accessToken): void
    {
        $this->configuration->set('SENDY_ACCESS_TOKEN', $accessToken, ShopConstraint::allShops());
    }

    public function getRefreshToken(): ?string
    {
        return $this->configuration->get('SENDY_REFRESH_TOKEN') ?: null;
    }

    public function setRefreshToken(string $refreshToken): void
    {
        $this->configuration->set('SENDY_REFRESH_TOKEN', $refreshToken, ShopConstraint::allShops());
    }

    public function getTokenExpires(): ?int
    {
        return $this->configuration->getInt('SENDY_TOKEN_EXPIRES') ?: null;
    }

    public function setTokenExpires(int $expires): void
    {
        $this->configuration->set('SENDY_TOKEN_EXPIRES', $expires, ShopConstraint::allShops());
    }

    public function pullOAuthState(): ?string
    {
        $state = $this->configuration->get('SENDY_OAUTH_STATE') ?: null;

        $this->configuration->set('SENDY_OAUTH_STATE', null, ShopConstraint::allShops());

        return $state;
    }

    public function setOAuthState(string $state): void
    {
        $this->configuration->set('SENDY_OAUTH_STATE', $state, ShopConstraint::allShops());
    }

    public function forgetAccessToken(): void
    {
        $this->configuration->set('SENDY_ACCESS_TOKEN', null, ShopConstraint::allShops());
        $this->configuration->set('SENDY_REFRESH_TOKEN', null, ShopConstraint::allShops());
        $this->configuration->set('SENDY_TOKEN_EXPIRES', null, ShopConstraint::allShops());
    }

    public function getDisplayTrackAndTraceColumn(): bool
    {
        return $this->configuration->getBoolean('SENDY_DISPLAY_TRACK_AND_TRACE_COLUMN', true);
    }

    public function setDisplayTrackAndTraceColumn(bool $displayTrackAndTraceColumn): void
    {
        $this->configuration->set('SENDY_DISPLAY_TRACK_AND_TRACE_COLUMN', $displayTrackAndTraceColumn, ShopConstraint::allShops());
    }

    public function setWebhookId(?string $id): void
    {
        $this->configuration->set('SENDY_WEBHOOK_ID', $id, ShopConstraint::allShops());
    }

    public function getWebhookId(): ?string
    {
        return $this->configuration->get('SENDY_WEBHOOK_ID') ?: null;
    }

    public function getSendySystemUserId(): ?int
    {
        return $this->configuration->getInt('SENDY_SYSTEM_USER_ID') ?: null;
    }

    public function getLastCronRun(): ?int
    {
        return $this->configuration->getInt('SENDY_LAST_CRON_RUN') ?: null;
    }

    public function setLastCronRun(int $now): void
    {
        $this->configuration->set('SENDY_LAST_CRON_RUN', $now, ShopConstraint::allShops());
    }
}
