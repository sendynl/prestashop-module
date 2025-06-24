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

namespace Sendy\PrestaShop\Controllers\Admin\Orders;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopLogger;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GenerateLabelController extends FrameworkBundleAdminController
{
    public function __invoke(Request $request): Response
    {
        PrestaShopLogger::addLog('Sendy - GenerateLabelController');

        $this->addFlash('info', $this->trans('This action will generate labels.', 'Modules.Sendy.Admin'));

        return new RedirectResponse(
            $request->headers->get('referer', $this->generateUrl('admin_orders_index'))
        );
    }
}
