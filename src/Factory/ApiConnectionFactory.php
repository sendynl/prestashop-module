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

namespace Sendy\PrestaShop\Factory;

use Sendy\PrestaShop\Exception\TokensMissingException;
use Sendy\PrestaShop\Repository\ConfigurationRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ApiConnectionFactory
{
    private ConfigurationRepository $configurationRepository;
    private UrlGeneratorInterface $router;

    public function __construct(ConfigurationRepository $configurationRepository, UrlGeneratorInterface $router)
    {
        $this->configurationRepository = $configurationRepository;
        $this->router = $router;
    }

    public function buildConnection(): \Sendy\Api\Connection
    {
        $connection = new \Sendy\Api\Connection();

        $connection
            ->setOauthClient(true)
            ->setClientId($this->configurationRepository->ensureClientId())
            ->setClientSecret($this->configurationRepository->ensureClientSecret())
            ->setRedirectUrl($this->getRedirectUrl())
            ->setUserAgentAppendix('PrestaShop/' . _PS_VERSION_)
            ->setTokenUpdateCallback(function (\Sendy\Api\Connection $connection) {
                $this->configurationRepository->setAccessToken($connection->getAccessToken());
                $this->configurationRepository->setRefreshToken($connection->getRefreshToken());
                $this->configurationRepository->setTokenExpires($connection->getTokenExpires());
            });

        return $connection;
    }

    public function buildConnectionUsingCode(string $code): \Sendy\Api\Connection
    {
        return $this->buildConnection()->setAuthorizationCode($code);
    }

    /**
     * @throws TokensMissingException
     */
    public function buildConnectionUsingTokens(): \Sendy\Api\Connection
    {
        $accessToken = $this->configurationRepository->getAccessToken();
        $refreshToken = $this->configurationRepository->getRefreshToken();
        $tokenExpires = $this->configurationRepository->getTokenExpires();

        if (!$accessToken || !$refreshToken || !$tokenExpires) {
            throw new TokensMissingException('Cannot build connection without access token, refresh token, or token expiration.');
        }

        return $this->buildConnection()
            ->setAccessToken($accessToken)
            ->setRefreshToken($refreshToken)
            ->setTokenExpires($tokenExpires);
    }

    public function getRedirectUrl(): string
    {
        return \ToolsCore::getShopDomainSsl(true) . $this->router->generate('sendy_login_callback');
    }
}
