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

namespace Sendy\PrestaShop\Controller\Admin\Orders;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Sendy\PrestaShop\Repository\ConfigurationRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class ColumnsController extends FrameworkBundleAdminController
{
    private ConfigurationRepository $configurationRepository;

    public function __construct(ConfigurationRepository $configurationRepository)
    {
        if (method_exists(parent::class, '__construct')) {
            parent::__construct();
        }

        $this->configurationRepository = $configurationRepository;
    }

    public function toggleTrackAndTraceColumn(): Response
    {
        $this->configurationRepository->setDisplayTrackAndTraceColumn(
            !$this->configurationRepository->getDisplayTrackAndTraceColumn()
        );

        return new RedirectResponse($this->generateUrl('admin_orders_index'));
    }

    public function toggleShippingMethodColumn(): Response
    {
        $this->configurationRepository->setDisplayShippingMethodColumn(
            !$this->configurationRepository->getDisplayShippingMethodColumn()
        );

        return new RedirectResponse($this->generateUrl('admin_orders_index'));
    }
}
