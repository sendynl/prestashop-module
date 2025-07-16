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
use Country;
use DateTime;
use Language;
use Order;
use Product;
use Sendy\Api\Connection;
use Sendy\Api\Exceptions\SendyException;
use Sendy\PrestaShop\Factory\ApiConnectionFactory;
use Sendy\PrestaShop\Repository\ConfigurationRepository;
use Sendy\PrestaShop\Support\Addr;

class CreateShipmentFromOrder
{
    private ApiConnectionFactory $apiConnectionFactory;

    private Connection $sendy;
    private ConfigurationRepository $configurationRepository;

    public function __construct(ApiConnectionFactory $apiConnectionFactory, ConfigurationRepository $configurationRepository)
    {
        $this->apiConnectionFactory = $apiConnectionFactory;
        $this->configurationRepository = $configurationRepository;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws SendyException
     */
    public function execute(Order $order, string $shopId, string $preferenceId, int $amount = 1): array
    {
        $this->sendy ??= $this->apiConnectionFactory->buildConnectionUsingTokens();

        $address = new Address((int) $order->id_address_delivery);

        $parsedAddress = Addr::parseAddress($address->address1);

        $data = [
            'shop_id' => $shopId,
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
        ];

        if ($this->configurationRepository->getImportWeight()) {
            $data['weight'] = $order->getTotalWeight();
        }

        if ($this->configurationRepository->getImportProducts()) {
            $data['products'] = $this->formatProducts($order);
        }

        return $this->sendy->shipment->createFromPreference($data, true);
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
