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

namespace Sendy\PrestaShop\Settings;

use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\ConfigurationInterface;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;
use Sendy\PrestaShop\Enums\MarkOrderAsCompletedSetting;
use Sendy\PrestaShop\Enums\ProcessingMethod;

/**
 * Manages storage and retrieval of the settings for the Sendy module.
 *
 * @phpstan-type SendySettingsData array{
 *      sendy_processing_method: string,
 *      sendy_processable_status: string|null,
 *      sendy_default_shop: string|null,
 *      sendy_import_products: bool,
 *      sendy_import_weight: bool,
 *      sendy_mark_order_as_completed: string
 *  }
 */
class SettingsDataManager implements DataConfigurationInterface, FormDataProviderInterface
{
    private ConfigurationInterface $configuration;

    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Returns the configuration data for the Sendy module.
     *
     * @return SendySettingsData
     */
    public function getConfiguration(): array
    {
        return [
            'sendy_processing_method' => $this->configuration->get('sendy_processing_method') ?? ProcessingMethod::PrestaShop,
            'sendy_processable_status' => $this->configuration->get('sendy_processable_status'),
            'sendy_default_shop' => $this->configuration->get('sendy_default_shop'),
            'sendy_import_products' => (bool) $this->configuration->get('sendy_import_products'),
            'sendy_import_weight' => (bool) $this->configuration->get('sendy_import_weight'),
            'sendy_mark_order_as_completed' => $this->configuration->get('sendy_mark_order_as_completed') ?? MarkOrderAsCompletedSetting::Manually,
        ];
    }

    /**
     * @param array<string, mixed> $configuration
     *
     * @return list<string> Contains error messages if the configuration is invalid
     */
    public function updateConfiguration(array $configuration): array
    {
        $errors = $this->getValidationErrors($configuration);

        if (!empty($errors)) {
            return $errors;
        }

        foreach ($configuration as $key => $value) {
            $this->configuration->set($key, $value);
        }

        return [];
    }

    /**
     * @param array<string, mixed> $configuration
     *
     * @return ($configuration is SendySettingsData ? true : false)
     */
    public function validateConfiguration(array $configuration): bool
    {
        return empty($this->getValidationErrors($configuration));
    }

    public function getData()
    {
        return $this->getConfiguration();
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string> Contains error messages if the configuration is invalid
     */
    public function setData(array $data)
    {
        return $this->updateConfiguration($data);
    }

    /**
     * @param array<string, mixed> $configuration
     *
     * @return array<string> Contains error messages if the configuration is invalid
     */
    private function getValidationErrors($configuration): array
    {
        $errors = [];

        if (!isset($configuration['sendy_processing_method'])) {
            $errors[] = 'Processing method is required.';
        } elseif (!in_array($configuration['sendy_processing_method'], ProcessingMethod::values(), true)) {
            $errors[] = 'Invalid processing method.';
        }

        // if (!isset($configuration['sendy_processable_status'])) {
        //    $errors[] = 'Processable status is required.';
        // } elseif (!is_string($configuration['sendy_processable_status'])) {
        //    $errors[] = 'Invalid processable status.';
        // }
        //
        // if (!isset($configuration['sendy_default_shop'])) {
        //    $errors[] = 'Default shop is required.';
        // } elseif (!is_string($configuration['sendy_default_shop'])) {
        //    $errors[] = 'Invalid default shop.';
        // }

        if (!isset($configuration['sendy_import_products'])) {
            $errors[] = 'Import products setting is required.';
        } elseif (!is_bool($configuration['sendy_import_products'])) {
            $errors[] = 'Invalid value for import products.';
        }

        if (!isset($configuration['sendy_import_weight'])) {
            $errors[] = 'Import weight setting is required.';
        } elseif (!is_bool($configuration['sendy_import_weight'])) {
            $errors[] = 'Invalid value for import weight.';
        }

        if (!isset($configuration['sendy_mark_order_as_completed'])) {
            $errors[] = 'Mark order as completed setting is required.';
        } elseif (!in_array($configuration['sendy_mark_order_as_completed'], MarkOrderAsCompletedSetting::values(), true)) {
            $errors[] = 'Invalid mark order as completed setting.';
        }

        return $errors;
    }
}
