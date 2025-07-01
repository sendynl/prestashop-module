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

use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;
use PrestaShop\PrestaShop\Core\Form\Handler;
use PrestaShop\PrestaShop\Core\Hook\HookDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;

class SettingsFormHandler extends Handler
{
    public function __construct(
        FormFactoryInterface $formFactory,
        HookDispatcherInterface $hookDispatcher,
        FormDataProviderInterface $formDataProvider,
        string $form = SettingsForm::class,
        $hookName = 'SendySettings',
        $formName = 'form'
    ) {
        parent::__construct($formFactory, $hookDispatcher, $formDataProvider, $form, $hookName, $formName);
    }
}
