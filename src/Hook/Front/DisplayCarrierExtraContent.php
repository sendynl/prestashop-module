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

namespace Sendy\PrestaShop\Hook\Front;

use Sendy\PrestaShop\Legacy\SendyCarrierConfig;
use Sendy\PrestaShop\Legacy\SendyCartParcelShop;

/**
 * Displays a parcel shop picker button after selecting a supported carrier during checkout.
 *
 * @see https://devdocs.prestashop-project.org/9/modules/concepts/hooks/list-of-hooks/displaycarrierextracontent/
 *
 * @todo This hook only gets executed for carriers that belong to the Sendy module. Add a way to add parcel shop
 *       carriers from the module settings. This cannot be done as an installation step, as it should be possible to add
 *       multiple parcel shop carriers per zone.
 */
final class DisplayCarrierExtraContent
{
    public function __construct()
    {
    }

    /**
     * @param array{
     *     carrier: array<string, mixed>,
     *     cart: \Cart,
     * } $params
     *
     * @return string
     */
    public function __invoke($params)
    {
        $carrierConfig = SendyCarrierConfig::getByReferenceId($params['carrier']['id_reference']);

        if ($carrierConfig === null) {
            return '';
        }

        if (!$carrierConfig->carrierRequiresParcelShop()) {
            return '';
        }

        $text = 'Kies een afhaalpunt';
        $parcelShopName = '';
        $cartParcelShop = SendyCartParcelShop::getByCartId($params['cart']->id);
        if ($cartParcelShop !== null) {
            $text = 'Verander afhaalpunt';
            $parcelShopName = htmlspecialchars($cartParcelShop->parcel_shop_name);
        }

        return <<<HTML
        <div
            data-sendy-parcel-shop-picker-carrier="{$carrierConfig->parcel_shop_carrier}"
            data-sendy-id-address-delivery="{$params['cart']->id_address_delivery}"
            class="sendy-parcel-shop-picker"
            style="margin-bottom: .9375rem;">
            <button type="button" class="btn btn-secondary sendy-parcel-shop-picker-button">{$text}</button>
            <div class="sendy-selected-parcel-shop-name">{$parcelShopName}</div>
        </div>
        HTML;
    }
}
