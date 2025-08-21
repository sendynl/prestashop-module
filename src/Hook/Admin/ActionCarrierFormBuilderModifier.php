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

namespace Sendy\PrestaShop\Hook\Admin;

use Carrier;
use Sendy\PrestaShop\Form\Carrier\CarrierForm;
use Symfony\Component\Form\Extension\Core\Type\FormType;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Modifies the carrier form to add a 'Sendy' tab with configuration fields.
 *
 * @see https://devdocs.prestashop-project.org/9/modules/concepts/hooks/list-of-hooks/actionformnameformbuildermodifier/
 */
final class ActionCarrierFormBuilderModifier
{
    private CarrierForm $carrierForm;

    public function __construct(CarrierForm $carrierForm)
    {
        $this->carrierForm = $carrierForm;
    }

    /**
     * @param array{
     *     form_builder: \Symfony\Component\Form\FormBuilderInterface,
     *     data: array<string, mixed>,
     *     options: array<string, mixed>,
     *     id: ?int,
     * } $params
     */
    public function __invoke($params): void
    {
        $carrier = new \Carrier($params['id']);

        // Only display the Sendy tab if the carrier belongs to the Sendy module
        if ($carrier->external_module_name !== 'sendy') {
            return;
        }

        // Add a 'Sendy' tab to the carrier form
        $params['form_builder']->add(
            'sendy',
            FormType::class,
            [
                'label' => 'Sendy',
                'required' => false,
            ]
        );

        // Add fields to the 'Sendy' tab
        $this->carrierForm->buildForm($params['form_builder']->get('sendy'), []);
    }
}
