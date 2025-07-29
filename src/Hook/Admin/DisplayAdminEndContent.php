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

use Sendy\PrestaShop\Form\CreateShipment\CreateShipmentFormHandler;
use Twig\Environment;

/**
 * Displays the modals at the end of the admin page.
 */
final class DisplayAdminEndContent
{
    private Environment $twig;
    private CreateShipmentFormHandler $createShipmentFormHandler;

    public function __construct(Environment $twig, CreateShipmentFormHandler $createShipmentFormHandler)
    {
        $this->twig = $twig;
        $this->createShipmentFormHandler = $createShipmentFormHandler;
    }

    public function __invoke(array $params): string
    {
        if ($params['route'] === 'admin_orders_index') {
            return $this->twig->render(
                '@Modules/sendy/views/templates/admin/order_modal.html.twig',
                [
                    'createShipmentFormView' => $this->createShipmentFormHandler->getForm()->createView(),
                ]
            );
        }

        return '';
    }
}
