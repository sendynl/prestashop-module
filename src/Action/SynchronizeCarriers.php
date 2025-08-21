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

namespace Sendy\PrestaShop\Action;

use Sendy\Api\Exceptions\SendyException;
use Sendy\PrestaShop\Exception\TokensMissingException;
use Sendy\PrestaShop\Factory\ApiConnectionFactory;
use Sendy\PrestaShop\Repository\CarrierRepository;
use Sendy\PrestaShop\Repository\ShopConfigurationRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SynchronizeCarriers
{
    private ApiConnectionFactory $apiConnectionFactory;
    private CarrierRepository $carrierRepository;
    private ShopConfigurationRepository $shopConfigurationRepository;

    public function __construct(
        ApiConnectionFactory $apiConnectionFactory,
        CarrierRepository $carrierRepository,
        ShopConfigurationRepository $shopConfigurationRepository
    ) {
        $this->apiConnectionFactory = $apiConnectionFactory;
        $this->carrierRepository = $carrierRepository;
        $this->shopConfigurationRepository = $shopConfigurationRepository;
    }

    /**
     * @throws TokensMissingException
     * @throws SendyException
     */
    public function execute(): void
    {
        if (!$this->shopConfigurationRepository->anyShopsUsingSendyProcessingMethod()) {
            return;
        }

        $carriers = array_map(fn (array $carrier) => [
            'external_id' => $carrier['id_reference'] ?: $carrier['id_carrier'],
            'name' => $carrier['name'] . ' - ' . $carrier['delay'],
        ], $this->carrierRepository->all());

        $sendy = $this->apiConnectionFactory->buildConnectionUsingTokens();
        $sendy->put('/webshop_shipping_methods', [
            'shipping_methods' => $carriers,
        ]);
    }
}
