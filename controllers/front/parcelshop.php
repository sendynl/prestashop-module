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

use Sendy\PrestaShop\Legacy\SendyCartParcelShop;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SendynlParcelshopModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        try {
            $this->ajax = true;

            $carrierReferenceId = Tools::getValue('carrier_reference_id');

            $body = json_decode(Tools::file_get_contents('php://input'), true);

            if (!$carrierReferenceId) {
                $this->clientError($this->trans('Carrier ID is required.', [], 'Modules.Sendynl.Front'));

                return;
            }

            if (!isset($body['parcel_shop_id'])) {
                $this->clientError($this->trans('Parcel shop ID is required.', [], 'Modules.Sendynl.Front'));

                return;
            }

            $cart = Context::getContext()->cart;

            if (!$cart || !$cart->id) {
                $this->clientError($this->trans('Cart not found.', [], 'Modules.Sendynl.Front'));

                return;
            }

            $cartParcelShop = SendyCartParcelShop::getOrNewByCartId($cart->id);
            $cartParcelShop->id_reference = $carrierReferenceId;
            $cartParcelShop->parcel_shop_id = (string) $body['parcel_shop_id'];
            $cartParcelShop->parcel_shop_name = $body['parcel_shop_name'];
            $cartParcelShop->parcel_shop_address = $body['parcel_shop_address'];
            $cartParcelShop->save();

            $this->ajaxRender(json_encode($cartParcelShop->toArray()));
        } catch (Throwable $e) {
            http_response_code(500);
            throw $e;
        }
    }

    private function clientError(string $message): void
    {
        $this->errors[] = $message;
        http_response_code(400);
        $this->ajaxRender(json_encode(['errors' => $this->errors]));
    }
}
