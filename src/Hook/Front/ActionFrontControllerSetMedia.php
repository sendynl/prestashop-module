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

namespace Sendy\PrestaShop\Hook\Front;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Registers the JavaScript file for the front office.
 *
 * @see https://devdocs.prestashop-project.org/9/modules/concepts/hooks/list-of-hooks/actionfrontcontrollersetmedia/
 */
final class ActionFrontControllerSetMedia
{
    public function __invoke(\Sendy $module): void
    {
        $controller = \Context::getContext()->controller;

        if ($controller->php_self === 'order') {
            $controller->registerJavascript('sendy_checkout', 'modules/sendy/views/js/front/checkout.js');
            $controller->registerStylesheet('sendy_styles', 'modules/sendy/views/css/front.css');

            $controller->registerJavascript('sendy_api', 'https://app.sendy.nl/embed/api.js', [
                'server' => 'remote',
            ]);
        }
    }
}
