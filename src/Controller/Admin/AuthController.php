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

namespace Sendy\PrestaShop\Controller\Admin;

use Context;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Sendy\Api\Exceptions\SendyException;
use Sendy\PrestaShop\Factory\ApiConnectionFactory;
use Sendy\PrestaShop\Repository\ConfigurationRepository;
use Sendy\PrestaShop\Support\Str;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AuthController extends FrameworkBundleAdminController
{
    private ConfigurationRepository $configurationRepository;
    private ApiConnectionFactory $apiConnectionFactory;
    private UrlGeneratorInterface $router;

    public function __construct(ConfigurationRepository $configurationRepository, ApiConnectionFactory $apiConnectionFactory, UrlGeneratorInterface $router)
    {
        $this->configurationRepository = $configurationRepository;
        $this->apiConnectionFactory = $apiConnectionFactory;
        $this->router = $router;
    }

    public function login(Request $request): Response
    {
        $this->configurationRepository->setOAuthState($state = Str::random(32));

        return new RedirectResponse('https://app.sendy.nl/plugin/initialize?' . http_build_query([
            'client_id' => $this->configurationRepository->ensureClientId(),
            'client_secret' => $this->configurationRepository->ensureClientSecret(),
            'redirect_uri' => $this->apiConnectionFactory->getRedirectUrl(),
            'name' => Context::getContext()->shop->name,
            'type' => 'prestashop',
            'state' => $state,
        ]));
    }

    public function callback(Request $request): Response
    {
        if ($this->configurationRepository->pullOAuthState() !== $request->query->get('state')) {
            $this->addFlash('error', $this->trans('Login failed. Please try again.', 'Modules.Sendy.Admin'));

            return new RedirectResponse($this->router->generate('sendy_settings'));
        }

        try {
            $sendy = $this->apiConnectionFactory->buildConnectionUsingCode($request->query->get('code'));

            $sendy->checkOrAcquireAccessToken();

            $this->addFlash('success', $this->trans('Login successful.', 'Modules.Sendy.Admin'));
        } catch (SendyException $exception) {
            $this->addFlash('error', $this->trans('Login failed: %error%', 'Modules.Sendy.Admin', [
                '%error%' => $exception->getMessage(),
            ]));
        }

        return new RedirectResponse($this->router->generate('sendy_settings'));
    }

    public function logout(Request $request): Response
    {
        // todo revoke token

        $this->configurationRepository->forgetAccessToken();

        return new RedirectResponse($this->router->generate('sendy_settings'));
    }
}
