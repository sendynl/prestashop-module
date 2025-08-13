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

namespace Sendy\PrestaShop\Hook\Admin;

use Context;
use Media;
use Sendy;
use Sendy\PrestaShop\Action\RunScheduledTasks;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Registers the JavaScript file for the back office.
 */
final class ActionAdminControllerSetMedia
{
    private UrlGeneratorInterface $router;
    private RunScheduledTasks $runScheduledTasks;

    public function __construct(UrlGeneratorInterface $router, RunScheduledTasks $runScheduledTasks)
    {
        $this->router = $router;
        $this->runScheduledTasks = $runScheduledTasks;
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

        $this->runScheduledTasks->execute();
    }
}
