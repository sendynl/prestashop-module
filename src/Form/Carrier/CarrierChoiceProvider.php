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

namespace Sendy\PrestaShop\Form\Carrier;

use PrestaShop\PrestaShop\Core\Form\ConfigurableFormChoiceProviderInterface;
use PrestaShop\PrestaShop\Core\Form\FormChoiceProviderInterface;
use Sendy\PrestaShop\Enum\Carrier;

class CarrierChoiceProvider implements FormChoiceProviderInterface, ConfigurableFormChoiceProviderInterface
{
    public function getChoices(array $options = [])
    {
        return Carrier::choices();
    }
}
