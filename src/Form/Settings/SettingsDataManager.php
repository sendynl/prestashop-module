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

namespace Sendy\PrestaShop\Form\Settings;

use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;
use Sendy\PrestaShop\Enum\ProcessingMethod;
use Sendy\PrestaShop\Repository\ConfigurationRepository;

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
    private ConfigurationRepository $configurationRepository;

    public function __construct(ConfigurationRepository $configurationRepository)
    {
        $this->configurationRepository = $configurationRepository;
    }

    /**
     * Returns the configuration data for the Sendy module.
     *
     * @return SendySettingsData
     */
    public function getConfiguration(): array
    {
        return [
            'sendy_processing_method' => $this->configurationRepository->getProcessingMethod(),
            'sendy_processable_status' => $this->configurationRepository->getProcessableStatus(),
            'sendy_default_shop' => $this->configurationRepository->getDefaultShop(),
            'sendy_import_products' => $this->configurationRepository->getImportProducts(),
            'sendy_import_weight' => $this->configurationRepository->getImportWeight(),
            'sendy_status_generated' => $this->configurationRepository->getStatusGenerated(),
            'sendy_status_printed' => $this->configurationRepository->getStatusPrinted(),
            'sendy_status_delivered' => $this->configurationRepository->getStatusDelivered(),
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

        $this->configurationRepository->setProcessingMethod($configuration['sendy_processing_method']);
        $this->configurationRepository->setProcessableStatus($configuration['sendy_processable_status']);
        $this->configurationRepository->setDefaultShop($configuration['sendy_default_shop']);
        $this->configurationRepository->setImportProducts($configuration['sendy_import_products']);
        $this->configurationRepository->setImportWeight($configuration['sendy_import_weight']);
        $this->configurationRepository->setStatusGenerated($configuration['sendy_status_generated']);
        $this->configurationRepository->setStatusPrinted($configuration['sendy_status_printed']);
        $this->configurationRepository->setStatusDelivered($configuration['sendy_status_delivered']);

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
     * @return ($configuration is SendySettingsData ? [] : list<string>)
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

        return $errors;
    }
}
