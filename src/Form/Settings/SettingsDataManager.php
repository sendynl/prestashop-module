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

namespace Sendy\PrestaShop\Form\Settings;

use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;
use PrestaShopBundle\Translation\TranslatorInterface;
use Sendy\PrestaShop\Enum\ProcessingMethod;
use Sendy\PrestaShop\Repository\ShopConfigurationRepository;
use Sendy\PrestaShop\Support\TypeTransformer;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Manages storage and retrieval of the settings for the Sendy module.
 *
 * @phpstan-type SendySettingsData array{
 *      sendy_processing_method: string,
 *      sendy_processable_status: string|null,
 *      sendy_default_shop: string|null,
 *      sendy_import_products: bool,
 *      sendy_import_weight: bool,
 *      sendy_status_generated: int|null,
 *      sendy_status_printed: int|null,
 *      sendy_status_delivered: int|null
 *  }
 */
class SettingsDataManager implements DataConfigurationInterface, FormDataProviderInterface
{
    private ShopConfigurationRepository $configurationRepository;
    private TranslatorInterface $translator;

    public function __construct(ShopConfigurationRepository $configurationRepository, TranslatorInterface $translator)
    {
        $this->configurationRepository = $configurationRepository;
        $this->translator = $translator;
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
        $this->configurationRepository->setProcessableStatus(
            TypeTransformer::toNullableInt($configuration['sendy_processable_status'])
        );
        $this->configurationRepository->setDefaultShop($configuration['sendy_default_shop']);
        $this->configurationRepository->setImportProducts($configuration['sendy_import_products']);
        $this->configurationRepository->setImportWeight($configuration['sendy_import_weight']);
        $this->configurationRepository->setStatusGenerated(
            TypeTransformer::toNullableInt($configuration['sendy_status_generated'])
        );
        $this->configurationRepository->setStatusPrinted(
            TypeTransformer::toNullableInt($configuration['sendy_status_printed'])
        );
        $this->configurationRepository->setStatusDelivered(
            TypeTransformer::toNullableInt($configuration['sendy_status_delivered'])
        );

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

    /**
     * @return SendySettingsData
     */
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
     * @return ($configuration is SendySettingsData ? array{} : list<string>)
     */
    private function getValidationErrors($configuration): array
    {
        $errors = [];

        if (!isset($configuration['sendy_processing_method'])) {
            $errors[] = $this->translator->trans('Processing method is required.', [], 'Modules.Sendy.Admin');
        } elseif (!in_array($configuration['sendy_processing_method'], ProcessingMethod::values(), true)) {
            $errors[] = $this->translator->trans('Invalid processing method.', [], 'Modules.Sendy.Admin');
        } elseif ($configuration['sendy_processing_method'] === ProcessingMethod::Sendy) {
            if (!isset($configuration['sendy_processable_status']) || !is_numeric($configuration['sendy_processable_status'])) {
                $errors[] = $this->translator->trans(
                    'Processable status is required when processing method is Sendy.',
                    [],
                    'Modules.Sendy.Admin'
                );
            }

            if (!isset($configuration['sendy_default_shop']) || !is_string($configuration['sendy_default_shop'])) {
                $errors[] = $this->translator->trans(
                    'Default shop is required when processing method is Sendy.',
                    [],
                    'Modules.Sendy.Admin'
                );
            }
        }

        if (!isset($configuration['sendy_import_products'])) {
            $errors[] = $this->translator->trans('Import products setting is required.', [], 'Modules.Sendy.Admin');
        } elseif (!is_bool($configuration['sendy_import_products'])) {
            $errors[] = $this->translator->trans('Invalid value for import products.', [], 'Modules.Sendy.Admin');
        }

        if (!isset($configuration['sendy_import_weight'])) {
            $errors[] = $this->translator->trans('Import weight setting is required.', [], 'Modules.Sendy.Admin');
        } elseif (!is_bool($configuration['sendy_import_weight'])) {
            $errors[] = $this->translator->trans('Invalid value for import weight.', [], 'Modules.Sendy.Admin');
        }

        return $errors;
    }
}
