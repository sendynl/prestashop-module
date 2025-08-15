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

namespace Sendy\PrestaShop\Form\Settings;

use PrestaShop\PrestaShop\Core\Form\FormChoiceProviderInterface;
use Sendy\Api\Exceptions\SendyException;
use Sendy\PrestaShop\Exception\TokensMissingException;
use Sendy\PrestaShop\Factory\ApiConnectionFactory;

class ShopChoiceProvider implements FormChoiceProviderInterface
{
    private ApiConnectionFactory $apiConnectionFactory;

    public function __construct(ApiConnectionFactory $apiConnectionFactory)
    {
        $this->apiConnectionFactory = $apiConnectionFactory;
    }

    /**
     * @return array<string, string>
     *
     * @throws SendyException
     * @throws TokensMissingException
     */
    public function getChoices()
    {
        $sendy = $this->apiConnectionFactory->buildConnectionUsingTokens();
        $shops = $sendy->shop->list();
        $choices = [];

        foreach ($shops as $shop) {
            $choices[$shop['name']] = $shop['uuid'];
        }

        return $choices;
    }
}
