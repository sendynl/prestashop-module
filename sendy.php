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

use PrestaShop\PrestaShop\Core\Context\LegacyControllerContext;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Sendy extends CarrierModule
{
    protected bool $config_form = false;

    public function __construct()
    {
        $this->name = 'sendy';
        $this->tab = 'shipping_logistics';
        $this->version = '2.0.0';
        $this->author = 'Sendy B.V.';
        $this->need_instance = 1;

        /*
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Sendy');
        $this->description = $this->l('A PrestaShop module that connects your store to the Sendy platform');

        $this->ps_versions_compliancy = ['min' => '1.7.8', 'max' => _PS_VERSION_];
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install(): bool
    {
        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');

            return false;
        }

        $carrier = $this->addCarrier();
        $this->addZones($carrier);
        $this->addGroups($carrier);
        $this->addRanges($carrier);
        Configuration::updateValue('SENDY_LIVE_MODE', false);

        return parent::install()
            && $this->registerHook('header')
            && $this->registerHook('displayBackOfficeHeader')
            && $this->registerHook('updateCarrier')
            && $this->registerHook('actionCarrierProcess')
            && $this->registerHook('actionCarrierUpdate')
            && $this->registerHook('actionObjectOrderAddAfter')
            && $this->registerHook('actionOrderStatusPostUpdate')
            && $this->registerHook('actionOrderStatusUpdate')
            && $this->registerHook('actionValidateOrder')
            && $this->registerHook('displayBeforeCarrier')
            && $this->registerHook('displayCarrierExtraContent')
            && $this->registerHook('displayCarrierList')
            && $this->registerHook('displayOrderConfirmation');
    }

    public function uninstall(): bool
    {
        Configuration::deleteByName('SENDY_LIVE_MODE');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent(): string
    {
        /*
         * If values have been submitted in the form, process.
         */
        if (((bool) Tools::isSubmit('submitSendyModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm(): string
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSendyModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigForm()]);
    }

    /**
     * Create the structure of your form.
     *
     * @return array<string, mixed>
     */
    protected function getConfigForm(): array
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'SENDY_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'SENDY_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                    ],
                    [
                        'type' => 'password',
                        'name' => 'SENDY_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * Set values for the inputs.
     *
     * @return array<string, mixed>
     */
    protected function getConfigFormValues(): array
    {
        return [
            'SENDY_LIVE_MODE' => Configuration::get('SENDY_LIVE_MODE', true),
            'SENDY_ACCOUNT_EMAIL' => Configuration::get('SENDY_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'SENDY_ACCOUNT_PASSWORD' => Configuration::get('SENDY_ACCOUNT_PASSWORD', null),
        ];
    }

    /**
     * Save form data.
     */
    protected function postProcess(): void
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * @param mixed $params
     * @param mixed $shipping_cost
     *
     * @return float
     */
    public function getOrderShippingCost($params, $shipping_cost): float
    {
        return 0.0;
    }

    /**
     * @param mixed $params
     */
    public function getOrderShippingCostExternal($params): bool
    {
        return false;
    }

    /**
     * @return Carrier|false
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function addCarrier()
    {
        $carrier = new Carrier();

        $carrier->name = $this->l('My super carrier');
        $carrier->is_module = true;
        $carrier->active = 1;
        $carrier->range_behavior = 1;
        $carrier->need_range = 1;
        $carrier->shipping_external = true;
        $carrier->range_behavior = 0;
        $carrier->external_module_name = $this->name;
        $carrier->shipping_method = 2;

        foreach (Language::getLanguages() as $lang) {
            $carrier->delay[$lang['id_lang']] = $this->l('Super fast delivery');
        }

        if ($carrier->add() == true) {
            @copy(dirname(__FILE__) . '/views/img/carrier_image.jpg', _PS_SHIP_IMG_DIR_ . '/' . (int) $carrier->id . '.jpg');
            Configuration::updateValue('MYSHIPPINGMODULE_CARRIER_ID', (int) $carrier->id);

            return $carrier;
        }

        return false;
    }

    protected function addGroups(Carrier $carrier): void
    {
        $groups_ids = [];
        $groups = Group::getGroups(Context::getContext()->language->id);
        foreach ($groups as $group) {
            $groups_ids[] = $group['id_group'];
        }

        $carrier->setGroups($groups_ids);
    }

    protected function addRanges(Carrier $carrier): void
    {
        $range_price = new RangePrice();
        $range_price->id_carrier = $carrier->id;
        $range_price->delimiter1 = '0';
        $range_price->delimiter2 = '10000';
        $range_price->add();

        $range_weight = new RangeWeight();
        $range_weight->id_carrier = $carrier->id;
        $range_weight->delimiter1 = '0';
        $range_weight->delimiter2 = '10000';
        $range_weight->add();
    }

    protected function addZones(Carrier $carrier): void
    {
        $zones = Zone::getZones();

        foreach ($zones as $zone) {
            $carrier->addZone($zone['id_zone']);
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookDisplayBackOfficeHeader(): void
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader(): void
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    public function hookActionCarrierProcess(): void
    {
        /* Place your code here. */
    }

    public function hookActionCarrierUpdate(): void
    {
        /* Place your code here. */
    }

    public function hookActionObjectOrderAddAfter(): void
    {
        /* Place your code here. */
    }

    public function hookActionOrderStatusPostUpdate(): void
    {
        /* Place your code here. */
    }

    public function hookActionOrderStatusUpdate(): void
    {
        /* Place your code here. */
    }

    public function hookActionValidateOrder(): void
    {
        /* Place your code here. */
    }

    public function hookDisplayBeforeCarrier(): void
    {
        /* Place your code here. */
    }

    public function hookDisplayCarrierExtraContent(): void
    {
        /* Place your code here. */
    }

    public function hookDisplayCarrierList(): void
    {
        /* Place your code here. */
    }

    public function hookDisplayOrderConfirmation(): void
    {
        /* Place your code here. */
    }
}
