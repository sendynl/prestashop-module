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

namespace Sendy\PrestaShop\Action;

use Address;
use Carrier;
use Country;
use DateTime;
use Language;
use Order;
use Product;
use Sendy\Api\Connection;
use Sendy\Api\Exceptions\SendyException;
use Sendy\PrestaShop\Factory\ApiConnectionFactory;
use Sendy\PrestaShop\Legacy\SendyCartParcelShop;
use Sendy\PrestaShop\Repository\ShopConfigurationRepository;
use Sendy\PrestaShop\Support\Addr;

class CreateShipmentFromOrder
{
    private ApiConnectionFactory $apiConnectionFactory;

    private Connection $sendy;
    private ShopConfigurationRepository $shopConfigurationRepository;

    public function __construct(ApiConnectionFactory $apiConnectionFactory, ShopConfigurationRepository $shopConfigurationRepository)
    {
        $this->apiConnectionFactory = $apiConnectionFactory;
        $this->shopConfigurationRepository = $shopConfigurationRepository;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws SendyException
     */
    public function execute(Order $order, string $sendyShopId, ?string $preferenceId = null, int $amount = 1): array
    {
        $this->sendy ??= $this->apiConnectionFactory->buildConnectionUsingTokens();

        $address = new Address((int) $order->id_address_delivery);
        $parsedAddress = Addr::parseAddress($address->address1);
        $carrier = new Carrier((int) $order->id_carrier);

        $data = [
            'shop_id' => $sendyShopId,
            'preference_id' => $preferenceId,
            'company_name' => $order->getCustomer()->company,
            'contact' => $order->getCustomer()->firstname . ' ' . $order->getCustomer()->lastname,
            'street' => $parsedAddress['street'],
            'number' => $parsedAddress['number'],
            'addition' => $parsedAddress['addition'],
            'comment' => $address->address2,
            'postal_code' => $address->postcode,
            'city' => $address->city,
            'email' => $order->getCustomer()->email,
            'reference' => $order->reference,
            'amount' => $amount,
            'order_date' => (new DateTime($order->date_add))->format(DATE_RFC3339),
            'country' => Country::getIsoById((int) $address->id_country),
            'options' => [],
            'shippingMethodId' => $carrier->id_reference,
        ];

        if ($this->shopConfigurationRepository->getImportWeight($order->id_shop)) {
            $data['weight'] = $order->getTotalWeight();
        }

        if ($this->shopConfigurationRepository->getImportProducts($order->id_shop)) {
            $data['products'] = $this->formatProducts($order);
        }

        $cartParcelShop = SendyCartParcelShop::getForOrder($order);

        if ($cartParcelShop) {
            $data['options']['parcel_shop_id'] = $cartParcelShop->parcel_shop_id;
        }

        if ($preferenceId) {
            return $this->sendy->shipment->createFromPreference($data, true);
        } else {
            return $this->sendy->shipment->createWithSmartRules($data);
        }
    }

    private function formatProducts(Order $order): array
    {
        $english = Language::getIdByIso('en');

        return array_map(function ($product) use ($english) {
            $data = [
                'description' => $product['product_name'],
                'quantity' => $product['product_quantity'],
                'sku' => $product['reference'],
                'unit_price' => $product['unit_price_tax_incl'],
                'unit_weight' => $product['weight'],
            ];

            $englishProduct = new Product((int) $product['id_product'], false, $english);
            if (is_string($englishProduct->name) && trim($englishProduct->name) !== '') {
                $data['description_en'] = $englishProduct->name;
            }

            return $data;
        }, $order->getProducts());
    }
}
