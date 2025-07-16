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

namespace Sendy\PrestaShop\Hook;

use Context;
use Media;
use Sendy;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ActionAdminControllerSetMedia
{
    private UrlGeneratorInterface $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    public function __invoke(Sendy $module)
    {
        $controller = Context::getContext()->controller;

        if ($controller->php_self === 'AdminOrders') {
            $controller->addJS($module->getPathUri() . 'views/js/admin/orders.js');

            Media::addJsDef([
                'sendyRoutes' => [
                    'sendy_orders_print_label' => $this->router->generate('sendy_orders_print_label'),
                ],
            ]);
        }
    }
}
