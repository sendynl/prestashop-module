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

use Sendy\PrestaShop\Form\CreateShipment\CreateShipmentFormHandler;
use Twig\Environment;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Displays the modals at the end of the admin page.
 *
 * @see https://devdocs.prestashop-project.org/9/modules/concepts/hooks/list-of-hooks/displayadminendcontent/
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

    /**
     * @param array<string, mixed> $params
     */
    public function __invoke(array $params): string
    {
        if (\Context::getContext()->controller->php_self === 'AdminOrders') {
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
