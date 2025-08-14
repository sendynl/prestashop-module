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
use PrestaShop\PrestaShop\Adapter\Shop\Context as ShopContext;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Sendy\Api\Exceptions\SendyException;
use Sendy\PrestaShop\Action\SynchronizeWebhook;
use Sendy\PrestaShop\Factory\ApiConnectionFactory;
use Sendy\PrestaShop\Repository\ConfigurationRepository;
use Sendy\PrestaShop\Support\Str;
use Shop;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AuthController extends FrameworkBundleAdminController
{
    private ConfigurationRepository $configurationRepository;
    private ApiConnectionFactory $apiConnectionFactory;
    private UrlGeneratorInterface $router;
    private SynchronizeWebhook $synchronizeWebhook;
    private ShopContext $shopContext;

    public function __construct(
        ConfigurationRepository $configurationRepository,
        ApiConnectionFactory $apiConnectionFactory,
        UrlGeneratorInterface $router,
        SynchronizeWebhook $synchronizeWebhook,
        ShopContext $shopContext
    ) {
        $this->configurationRepository = $configurationRepository;
        $this->apiConnectionFactory = $apiConnectionFactory;
        $this->router = $router;
        $this->synchronizeWebhook = $synchronizeWebhook;
        $this->shopContext = $shopContext;
    }

    public function login(Request $request): Response
    {
        if (Shop::isFeatureActive() && !$this->shopContext->isAllShopContext()) {
            $this->addFlash('error', $this->trans("You can only log in to Sendy from the 'All stores' context.", 'Modules.Sendy.Admin'));

            return new RedirectResponse($this->router->generate('sendy_settings'));
        }

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
            // Build a client with token update callback
            $sendy = $this->apiConnectionFactory->buildConnectionUsingCode($request->query->get('code'));

            // Check if the access token is valid and acquire a new one if necessary
            $sendy->checkOrAcquireAccessToken();

            // Install/uninstall the webhook as needed
            $this->synchronizeWebhook->execute();

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
