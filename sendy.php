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

require_once __DIR__ . '/vendor/autoload.php';

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use Sendy\PrestaShop\Action\CreateShipmentFromOrder;
use Sendy\PrestaShop\Factory\ApiConnectionFactory;
use Sendy\PrestaShop\Form\Settings\LegacySettingsForm;
use Sendy\PrestaShop\Hook;
use Sendy\PrestaShop\Installer;
use Sendy\PrestaShop\Legacy\DummyUrlGenerator;
use Sendy\PrestaShop\Repository\ConfigurationRepository;
use Sendy\PrestaShop\Repository\ShopConfigurationRepository;

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

        return parent::install()
            && Installer\ConfigurationDefaults::install()
            && Installer\SystemUser::install()
            && Installer\Hooks::install($this);
    }

    public function uninstall(): bool
    {
        Configuration::deleteByName('SENDY_LIVE_MODE');

        include dirname(__FILE__) . '/sql/uninstall.php';

        return parent::uninstall()
            && Installer\SystemUser::uninstall();
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
        $this->get(Hook\Admin\ActionAdminControllerSetMedia::class)($this);
    }

    public function hookActionFrontControllerSetMedia()
    {
        (new Hook\Front\ActionFrontControllerSetMedia())($this);
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
        // $container = SymfonyContainer::getInstance();
        //
        // // When this hook is triggered from the front office (after an order is placed), we need to boot the Symfony
        // // kernel to get the container
        // if (!$container) {
        //    if (file_exists(_PS_ROOT_DIR_ . '/app/FrontKernel.php')) {
        //        require_once _PS_ROOT_DIR_ . '/app/FrontKernel.php';
        //        $kernel = new \FrontKernel('prod', false);
        //    } else {
        //        require_once _PS_ROOT_DIR_ . '/app/AppKernel.php';
        //        $kernel = new \AppKernel('prod', false);
        //    }
        //
        //    $kernel->boot();
        //    $container = $kernel->getContainer();
        // }
        //
        // $container->get(Hook\ActionOrderStatusPostUpdate::class)($params);

        $configurationRepository = new ConfigurationRepository(new PrestaShop\PrestaShop\Adapter\Configuration());
        $shopConfigurationRepository = new ShopConfigurationRepository(new PrestaShop\PrestaShop\Adapter\Configuration(), new PrestaShop\PrestaShop\Adapter\Shop\Context());
        $apiConnectionFactory = new ApiConnectionFactory($configurationRepository, new DummyUrlGenerator());
        $createShipmentFromOrder = new CreateShipmentFromOrder($apiConnectionFactory, $shopConfigurationRepository);

        (new Hook\ActionOrderStatusPostUpdate($createShipmentFromOrder, $shopConfigurationRepository))($params);
    }

    public function hookActionOrderStatusUpdate($params): void
    {
        PrestaShopLogger::addLog('Sendy - ActionOrderStatusUpdate hook - ' . print_r($params, true));
    }

    public function hookActionValidateOrder($params): void
    {
        PrestaShopLogger::addLog('Sendy - ActionValidateOrder hook - ' . print_r($params, true));
    }

    public function hookActionValidateStepComplete($params): void
    {
        (new Hook\Front\ActionValidateStepComplete())($params);
    }

    public function hookDisplayBeforeCarrier($params): void
    {
        PrestaShopLogger::addLog('Sendy - DisplayBeforeCarrier hook - ' . print_r($params, true));
    }

    public function hookDisplayCarrierExtraContent($params): string
    {
        return (new Hook\Front\DisplayCarrierExtraContent())($params);
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
        $this->get(Hook\Admin\ActionOrderGridDefinitionModifier::class)($params);
    }

    public function hookActionOrderGridQueryBuilderModifier(array $params): void
    {
        (new Hook\Admin\ActionOrderGridQueryBuilderModifier())($params);
    }

    public function hookDisplayAdminOrderSide($params): string
    {
        return $this->get(Hook\Admin\DisplayAdminOrderSide::class)($params);
    }

    public function hookDisplayAdminEndContent($params): string
    {
        return $this->get(Hook\Admin\DisplayAdminEndContent::class)($params);
    }

    public function hookActionCarrierFormBuilderModifier(array $params): void
    {
        $this->get(Hook\Admin\ActionCarrierFormBuilderModifier::class)($params);
    }

    public function hookActionAfterUpdateCarrierFormHandler(array $params): void
    {
        $this->get(Hook\Admin\ActionAfterUpdateCarrierFormHandler::class)($params);
    }

    public function hookActionAfterCreateCarrierFormHandler(array $params): void
    {
        $this->get(Hook\Admin\ActionAfterCreateCarrierFormHandler::class)($params);
    }

    public function hookActionCarrierFormDataProviderData(array $params): void
    {
        $this->get(Hook\Admin\ActionCarrierFormDataProviderData::class)($params);
    }
}
