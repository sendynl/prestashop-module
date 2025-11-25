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

require_once __DIR__ . '/vendor/autoload.php';

use Sendy\PrestaShop\Action\CreateShipmentFromOrder;
use Sendy\PrestaShop\Factory\ApiConnectionFactory;
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
        $this->version = '2.0.0a6';
        $this->author = 'Sendy B.V.';
        $this->need_instance = 1;
        $this->module_key = 'c4f781164993281f1b2e1e3147b96348';

        /*
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Sendy', [], 'Modules.Sendy.Admin');
        $this->description = $this->trans('A PrestaShop module that connects your store to the Sendy platform', [], 'Modules.Sendy.Admin');

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
        if (!extension_loaded('curl')) {
            $this->_errors[] = $this->trans('You have to enable the cURL extension on your server to install this module', [], 'Modules.Sendy.Admin');

            return false;
        }

        include dirname(__FILE__) . '/sql/install.php';

        return parent::install()
            && Installer\ConfigurationDefaults::install()
            && Installer\SystemUser::install()
            && Installer\Hooks::install($this);
    }

    public function uninstall(): bool
    {
        include dirname(__FILE__) . '/sql/uninstall.php';

        return parent::uninstall()
            && Installer\SystemUser::uninstall();
    }

    /**
     * Load the configuration form
     *
     * @return void
     *
     * @throws Exception
     */
    public function getContent()
    {
        /** @var Symfony\Component\Routing\Router $router */
        $router = $this->get('router');
        $route = $router->generate('sendy_settings');
        Tools::redirectAdmin($route);
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

    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    public function hookActionAdminControllerSetMedia(): void
    {
        try {
            $this->get(Hook\Admin\ActionAdminControllerSetMedia::class)($this);
        } catch (Exception $e) {
            PrestaShopLogger::addLog(
                'Sendy - ActionAdminControllerSetMedia - Error: ' . $e->getMessage(),
                PrestaShopLogger::LOG_SEVERITY_LEVEL_ERROR
            );
        }
    }

    public function hookActionFrontControllerSetMedia(): void
    {
        (new Hook\Front\ActionFrontControllerSetMedia())($this);
    }

    public function hookActionOrderStatusPostUpdate($params): void
    {
        $configurationRepository = new ConfigurationRepository(new PrestaShop\PrestaShop\Adapter\Configuration());
        $shopConfigurationRepository = new ShopConfigurationRepository(new PrestaShop\PrestaShop\Adapter\Configuration(), new PrestaShop\PrestaShop\Adapter\Shop\Context());
        $apiConnectionFactory = new ApiConnectionFactory($configurationRepository, new DummyUrlGenerator());
        $createShipmentFromOrder = new CreateShipmentFromOrder($apiConnectionFactory, $shopConfigurationRepository);

        (new Hook\ActionOrderStatusPostUpdate($createShipmentFromOrder, $shopConfigurationRepository))($params);
    }

    public function hookActionValidateStepComplete($params): void
    {
        (new Hook\Front\ActionValidateStepComplete())($params);
    }

    public function hookDisplayCarrierExtraContent($params): string
    {
        return (new Hook\Front\DisplayCarrierExtraContent())($params);
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

    public function hookActionObjectCarrierAddAfter(array $params): void
    {
        $this->get(Hook\Admin\ActionObjectCarrierAddAfter::class)($params);
    }

    public function hookActionObjectCarrierUpdateAfter(array $params): void
    {
        $this->get(Hook\Admin\ActionObjectCarrierUpdateAfter::class)($params);
    }

    public function hookActionObjectCarrierDeleteAfter(array $params): void
    {
        $this->get(Hook\Admin\ActionObjectCarrierDeleteAfter::class)($params);
    }
}
