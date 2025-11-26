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

use Sendy\PrestaShop\Legacy\SendyCarrierConfig;
use Sendy\PrestaShop\Legacy\SendyCartParcelShop;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Validates whether a parcel shop has been selected in the delivery step.
 *
 * @see https://devdocs.prestashop-project.org/9/modules/concepts/hooks/list-of-hooks/actionvalidatestepcomplete/
 */
final class ActionValidateStepComplete
{
    /**
     * @param array{
     *     step_name: string,
     *     request_params: array<string, mixed>,
     *     completed: bool,
     *     cookie: \Cookie,
     *     cart: \Cart,
     *     altern: int,
     * } $params
     */
    public function __invoke(array $params): void
    {
        if ($params['step_name'] !== 'delivery') {
            return;
        }

        $carrier = new \Carrier($params['cart']->id_carrier);

        $carrierConfig = SendyCarrierConfig::getByReferenceId((int) $carrier->id_reference);

        if ($carrierConfig === null || !$carrierConfig->carrierRequiresParcelShop()) {
            return;
        }

        $cartParcelShop = SendyCartParcelShop::getByCartId($params['cart']->id);

        if ($cartParcelShop === null || $cartParcelShop->id_reference !== $carrier->id_reference) {
            $params['completed'] = false;
            \Context::getContext()->controller->errors[] = \Context::getContext()->getTranslator()->trans(
                'This shipping method requires a parcel shop to be selected.',
                [],
                'Modules.Sendynl.Front'
            );
        }
    }
}
