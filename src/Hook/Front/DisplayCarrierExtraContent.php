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
 * Displays a parcel shop picker button after selecting a supported carrier during checkout.
 *
 * @see https://devdocs.prestashop-project.org/9/modules/concepts/hooks/list-of-hooks/displaycarrierextracontent/
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
    public function __invoke($params): string
    {
        $carrierConfig = SendyCarrierConfig::getByReferenceId((int) $params['carrier']['id_reference']);

        if ($carrierConfig === null) {
            return '';
        }

        if (!$carrierConfig->carrierRequiresParcelShop()) {
            return '';
        }

        $text = \Context::getContext()->getTranslator()->trans('Select a pick up point', [], 'Modules.Sendynl.Front');
        $parcelShopName = '';
        $parcelShopAddress = '';
        $cartParcelShop = SendyCartParcelShop::getByCartId($params['cart']->id);
        if ($cartParcelShop !== null && $cartParcelShop->id_reference === $params['carrier']['id_reference']) {
            $parcelShopName = htmlspecialchars($cartParcelShop->parcel_shop_name ?? '');
            $parcelShopAddress = htmlspecialchars($cartParcelShop->parcel_shop_address ?? '');
        }
        $parcelShopUrl = \Context::getContext()->link->getModuleLink(
            'sendynl',
            'parcelshop',
            [
                'carrier_reference_id' => $params['carrier']['id_reference'],
            ]
        );

        return <<<HTML
        <div
            data-sendynl-parcel-shop-picker-carrier="{$carrierConfig->parcel_shop_carrier}"
            data-sendynl-id-address-delivery="{$params['cart']->id_address_delivery}"
            data-sendynl-parcel-shop-url="{$parcelShopUrl}"
            class="sendynl-parcel-shop-picker col-xs-12"
        >
            <button type="button" class="btn btn-secondary sendynl-parcel-shop-picker-button">{$text}</button>
            <div class="sendynl-parcel-shop-picker-name">{$parcelShopName}</div>
            <div class="sendynl-parcel-shop-picker-address">{$parcelShopAddress}</div>
        </div>
        HTML;
    }
}
