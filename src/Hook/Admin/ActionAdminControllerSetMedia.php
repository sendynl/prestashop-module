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

namespace Sendy\PrestaShop\Hook\Admin;

use Sendy\PrestaShop\Action\RunScheduledTasks;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Registers the JavaScript file for the back office.
 *
 * @see https://devdocs.prestashop-project.org/9/modules/concepts/hooks/list-of-hooks/actionadmincontrollersetmedia/
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

    public function __invoke(\Sendy $module): void
    {
        $controller = \Context::getContext()->controller;

        if ($controller->php_self === 'AdminOrders') {
            $controller->addJS($module->getPathUri() . 'views/js/admin/orders.js');

            \Media::addJsDef([
                'sendyRoutes' => [
                    'sendy_orders_print_label' => $this->router->generate('sendy_orders_print_label'),
                ],
            ]);
        }

        $this->runScheduledTasks->execute();
    }
}
