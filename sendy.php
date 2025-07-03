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

use Sendy\PrestaShop\Forms\Settings\LegacySettingsForm;
use Sendy\PrestaShop\Hooks;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Sendy extends CarrierModule
{
    public const EDIT_SHIPMENT_URL = 'https://app.sendy.nl/shipment/%s/edit';
    public const VIEW_PACKAGE_URL = 'https://app.sendy.nl/packages/%s';

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

        $this->tabs = [
            [
                'name' => 'Sendy',
                'class_name' => 'AdminSendySettings',
                'visible' => true,
                'parent_class_name' => 'AdminParentShipping',
            ],
        ];
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

        include dirname(__FILE__) . '/sql/install.php';
        require_once __DIR__ . '/src/Hooks/HookInstaller.php';

        return parent::install()
            && Hooks\HookInstaller::registerHooks($this);
    }

    public function uninstall(): bool
    {
        Configuration::deleteByName('SENDY_LIVE_MODE');

        include dirname(__FILE__) . '/sql/uninstall.php';

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     *
     * @return string|void
     *
     * @throws Exception
     */
    public function getContent()
    {
        if (_PS_VERSION_ >= '1.7.8') {
            /** @var Symfony\Component\Routing\Router $router */
            $router = $this->get('router');
            $route = $router->generate('sendy_settings');
            Tools::redirectAdmin($route);
        } else {
            // TODO this can be removed along with any references to configuration values defined in this class
            $form = new LegacySettingsForm(
                $this->context->controller,
                $this->context->language,
                $this->context->smarty,
                $this->context->link,
                $this,
                $this->_path,
                $this->local_path,
                $this->table,
                $this->identifier
            );

            return $form->getContent();
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
            @copy(
                dirname(__FILE__) . '/views/img/carrier_image.jpg',
                _PS_SHIP_IMG_DIR_ . '/' . (int) $carrier->id . '.jpg'
            );
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

    public function hookActionAdminControllerSetMedia()
    {
        $this->get(Hooks\ActionAdminControllerSetMedia::class)($this);
    }

    public function hookActionFrontControllerSetMedia()
    {
        $this->get(Hooks\ActionFrontControllerSetMedia::class)($this);
    }

    public function hookActionCarrierProcess($params): void
    {
        PrestaShopLogger::addLog('Sendy - ActionCarrierProcess hook - ' . print_r($params, true));
    }

    public function hookActionCarrierUpdate($params): void
    {
        PrestaShopLogger::addLog('Sendy - ActionCarrierUpdate hook - ' . print_r($params, true));
    }

    public function hookActionObjectOrderAddAfter($params): void
    {
        PrestaShopLogger::addLog('Sendy - ActionObjectOrderAddAfter hook - ' . print_r($params, true));
    }

    public function hookActionOrderStatusPostUpdate($params): void
    {
        PrestaShopLogger::addLog('Sendy - ActionOrderStatusPostUpdate hook - ' . print_r($params, true));
    }

    public function hookActionOrderStatusUpdate($params): void
    {
        PrestaShopLogger::addLog('Sendy - ActionOrderStatusUpdate hook - ' . print_r($params, true));
    }

    public function hookActionValidateOrder($params): void
    {
        PrestaShopLogger::addLog('Sendy - ActionValidateOrder hook - ' . print_r($params, true));
    }

    public function hookDisplayBeforeCarrier($params): void
    {
        PrestaShopLogger::addLog('Sendy - DisplayBeforeCarrier hook - ' . print_r($params, true));
    }

    public function hookDisplayCarrierExtraContent($params): void
    {
        PrestaShopLogger::addLog('Sendy - DisplayCarrierExtraContent hook - ' . print_r($params, true));
    }

    public function hookDisplayCarrierList($params): void
    {
        PrestaShopLogger::addLog('Sendy - DisplayCarrierList hook - ' . print_r($params, true));
    }

    public function hookDisplayOrderConfirmation($params): void
    {
        PrestaShopLogger::addLog('Sendy - DisplayOrderConfirmation hook - ' . print_r($params, true));
    }

    public function hookActionOrderGridDefinitionModifier(array $params): void
    {
        $this->get(Hooks\ActionOrderGridDefinitionModifier::class)($params);
    }

    public function hookActionOrderGridQueryBuilderModifier(array $params): void
    {
        (new Hooks\ActionOrderGridQueryBuilderModifier())($params);
    }

    public function hookDisplayAdminOrderSide($params): string
    {
        return $this->get(Hooks\DisplayAdminOrderSide::class)($params);
    }
}
