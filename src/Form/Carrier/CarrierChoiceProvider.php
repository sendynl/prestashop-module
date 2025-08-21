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

namespace Sendy\PrestaShop\Form\Carrier;

use PrestaShop\PrestaShop\Core\Form\ConfigurableFormChoiceProviderInterface;
use PrestaShop\PrestaShop\Core\Form\FormChoiceProviderInterface;
use Sendy\PrestaShop\Enum\Carrier;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CarrierChoiceProvider implements FormChoiceProviderInterface, ConfigurableFormChoiceProviderInterface
{
    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, string>
     */
    public function getChoices(array $options = [])
    {
        return Carrier::choices();
    }
}
