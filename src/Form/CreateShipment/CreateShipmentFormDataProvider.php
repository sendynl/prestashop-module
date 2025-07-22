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

namespace Sendy\PrestaShop\Form\CreateShipment;

use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;
use Sendy\PrestaShop\Repository\ShopConfigurationRepository;

class CreateShipmentFormDataProvider implements FormDataProviderInterface
{
    private ShopConfigurationRepository $configurationRepository;

    public function __construct(ShopConfigurationRepository $configurationRepository)
    {
        $this->configurationRepository = $configurationRepository;
    }

    public function getData()
    {
        return [
            'shop_id' => $this->configurationRepository->getDefaultShop(),
            'preference_id' => null,
            'amount' => 1,
        ];
    }

    public function setData(array $data)
    {
        // not implemented - form is handled by Sendy\PrestaShop\Action\CreateShipmentFromOrder
    }
}
