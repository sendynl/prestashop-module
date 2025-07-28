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
use Sendy;

/**
 * Registers the JavaScript file for the front office.
 */
final class ActionFrontControllerSetMedia
{
    public function __invoke(Sendy $module)
    {
        $controller = Context::getContext()->controller;

        if ($controller->php_self === 'order') {
            $controller->addJS($module->getPathUri() . '/views/js/front/checkout.js?v=1');
            $controller->addJS('https://app.sendy.nl/embed/api.js');
        }
    }
}
