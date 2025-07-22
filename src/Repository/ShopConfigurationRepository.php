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

namespace Sendy\PrestaShop\Repository;

use InvalidArgumentException;
use PrestaShop\PrestaShop\Adapter\Shop\Context;
use PrestaShop\PrestaShop\Core\Domain\Configuration\ShopConfigurationInterface;
use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint;
use Sendy\PrestaShop\Enum\ProcessingMethod;

/**
 * This repository handles configuration that can be specific to a shop when multistore is enabled.
 */
class ShopConfigurationRepository
{
    private ShopConfigurationInterface $configuration;
    private Context $shopContext;

    public function __construct(ShopConfigurationInterface $configuration, Context $shopContext)
    {
        $this->configuration = $configuration;
        $this->shopContext = $shopContext;
    }

    /**
     * @return ProcessingMethod::*
     */
    public function getProcessingMethod(?int $storeId = null): string
    {
        return $this->configuration->get('SENDY_PROCESSING_METHOD', ProcessingMethod::PrestaShop, $this->getShopConstraint($storeId))
            ?: ProcessingMethod::PrestaShop;
    }

    public function setProcessingMethod(string $processingMethod): void
    {
        if (!in_array($processingMethod, ProcessingMethod::values(), true)) {
            throw new InvalidArgumentException(sprintf('Invalid processing method: %s', $processingMethod));
        }

        $this->configuration->set('SENDY_PROCESSING_METHOD', $processingMethod, $this->getShopConstraint());
    }

    public function getProcessableStatus(?int $storeId = null): ?int
    {
        return $this->configuration->getInt('SENDY_PROCESSABLE_STATUS', 0, $this->getShopConstraint($storeId)) ?: null;
    }

    public function setProcessableStatus(?int $processableStatus): void
    {
        $this->configuration->set('SENDY_PROCESSABLE_STATUS', $processableStatus, $this->getShopConstraint());
    }

    /**
     * Get the UUID of the Sendy shop that should be used to create shipments.
     */
    public function getDefaultShop(?int $storeId = null): ?string
    {
        return $this->configuration->get('SENDY_DEFAULT_SHOP', null, $this->getShopConstraint($storeId)) ?: null;
    }

    public function setDefaultShop(?string $sendyShopId): void
    {
        $this->configuration->set('SENDY_DEFAULT_SHOP', $sendyShopId, $this->getShopConstraint());
    }

    public function getImportProducts(?int $storeId = null): bool
    {
        return $this->configuration->getBoolean('SENDY_IMPORT_PRODUCTS', false, $this->getShopConstraint($storeId));
    }

    public function setImportProducts(bool $importProducts): void
    {
        $this->configuration->set('SENDY_IMPORT_PRODUCTS', $importProducts, $this->getShopConstraint());
    }

    public function getImportWeight(?int $storeId = null): bool
    {
        return $this->configuration->getBoolean('SENDY_IMPORT_WEIGHT', false, $this->getShopConstraint($storeId));
    }

    public function setImportWeight(bool $importWeight): void
    {
        $this->configuration->set('SENDY_IMPORT_WEIGHT', $importWeight, $this->getShopConstraint());
    }

    public function getStatusGenerated(): ?int
    {
        return $this->configuration->getInt('SENDY_STATUS_GENERATED', 0, $this->getShopConstraint()) ?: null;
    }

    public function setStatusGenerated(?int $statusId): void
    {
        $this->configuration->set('SENDY_STATUS_GENERATED', $statusId, $this->getShopConstraint());
    }

    public function getStatusPrinted(): ?int
    {
        return $this->configuration->getInt('SENDY_STATUS_PRINTED', 0, $this->getShopConstraint()) ?: null;
    }

    public function setStatusPrinted(?int $statusId): void
    {
        $this->configuration->set('SENDY_STATUS_PRINTED', $statusId, $this->getShopConstraint());
    }

    public function getStatusDelivered(): ?int
    {
        return $this->configuration->getInt('SENDY_STATUS_DELIVERED', 0, $this->getShopConstraint()) ?: null;
    }

    public function setStatusDelivered(?int $statusId): void
    {
        $this->configuration->set('SENDY_STATUS_DELIVERED', $statusId, $this->getShopConstraint());
    }

    private function getShopConstraint(?int $storeId = null): ShopConstraint
    {
        if ($storeId !== null) {
            return ShopConstraint::shop($storeId, true);
        }

        return $this->shopContext->getShopConstraint(true);
    }
}
