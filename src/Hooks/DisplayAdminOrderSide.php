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

namespace Sendy\PrestaShop\Hooks;

use Twig\Environment;

final class DisplayAdminOrderSide
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function __invoke(array $params): string
    {
        return $this->twig->render('@Modules/sendy/views/templates/admin/order_side.html.twig');
    }
}
