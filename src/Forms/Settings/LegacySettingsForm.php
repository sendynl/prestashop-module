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

namespace Sendy\PrestaShop\Forms\Settings;

use AdminController;
use Configuration;
use HelperForm;
use Language;
use Link;
use Sendy;
use Smarty;
use Tools;

class LegacySettingsForm
{
    private AdminController $controller;
    private Language $language;
    private Smarty $smarty;
    private Link $link;
    private Sendy $module;
    private string $path;
    private string $localPath;
    private string $table;
    private string $identifier;

    public function __construct(
        AdminController $controller,
        Language $language,
        Smarty $smarty,
        Link $link,
        Sendy $module,
        string $path,
        string $localPath,
        string $table,
        string $identifier
    ) {
        $this->language = $language;
        $this->smarty = $smarty;
        $this->link = $link;
        $this->module = $module;
        $this->path = $path;
        $this->localPath = $localPath;
        $this->table = $table;
        $this->identifier = $identifier;
        $this->controller = $controller;
    }

    public function getContent(): string
    {
        /*
         * If values have been submitted in the form, process.
         */
        if (((bool) Tools::isSubmit('submitSendyModule')) == true) {
            $this->postProcess();
        }

        $this->smarty->assign('module_dir', $this->path);

        $output = $this->smarty->fetch($this->localPath . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();
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
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm(): string
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this->module;
        $helper->default_form_language = $this->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSendyModule';
        $helper->currentIndex = $this->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->module->name . '&tab_module=' . $this->module->tab . '&module_name=' . $this->module->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->controller->getLanguages(),
            'id_language' => $this->language->id,
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
                    'title' => $this->module->l('Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->module->l('Live mode'),
                        'name' => 'SENDY_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->module->l('Use this module in live mode'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->module->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->module->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->module->l('Enter a valid email address'),
                        'name' => 'SENDY_ACCOUNT_EMAIL',
                        'label' => $this->module->l('Email'),
                    ],
                    [
                        'type' => 'password',
                        'name' => 'SENDY_ACCOUNT_PASSWORD',
                        'label' => $this->module->l('Password'),
                    ],
                ],
                'submit' => [
                    'title' => $this->module->l('Save'),
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
}
