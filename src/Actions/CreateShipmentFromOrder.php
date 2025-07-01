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

namespace Sendy\PrestaShop\Actions;

use Address;
use Country;
use DateTime;
use Order;
use Sendy\Api\Connection;
use Sendy\Api\Exceptions\SendyException;
use Sendy\PrestaShop\Factories\ApiConnectionFactory;
use Sendy\PrestaShop\Repositories\ConfigurationRepository;
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

        return $this->sendy->shipment->createFromPreference([
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
            'weight' => $this->configurationRepository->getImportWeight() ? $order->getTotalWeight() : null,
            'amount' => $amount,
            'order_date' => (new DateTime($order->date_add))->format(DATE_RFC3339),
            'country' => Country::getIsoById((int) $address->id_country),
        ]);
    }

    private function convertProducts(Order $order): array
    {
        return array_map(fn ($product) => [
            'sku' => $product['reference'] ?? null,
            // todo description, quantity, unit weight, unit price
        ], $order->getProducts());
    }
}
