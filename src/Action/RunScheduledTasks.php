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

namespace Sendy\PrestaShop\Action;

use Sendy\PrestaShop\Repository\ConfigurationRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class RunScheduledTasks
{
    private ConfigurationRepository $configurationRepository;
    private SynchronizeWebhook $synchronizeWebhook;
    private SynchronizeCarriers $synchronizeCarriers;

    public function __construct(
        ConfigurationRepository $configurationRepository,
        SynchronizeWebhook $synchronizeWebhook,
        SynchronizeCarriers $synchronizeCarriers
    ) {
        $this->configurationRepository = $configurationRepository;
        $this->synchronizeWebhook = $synchronizeWebhook;
        $this->synchronizeCarriers = $synchronizeCarriers;
    }

    public function execute(): void
    {
        $now = time();
        $oneDayAgo = $now - 86400;

        if ($this->configurationRepository->getLastCronRun() > $oneDayAgo) {
            return;
        }

        $this->configurationRepository->setLastCronRun($now);

        try {
            $this->synchronizeWebhook->execute();
            $this->synchronizeCarriers->execute();
        } catch (\Throwable $e) {
            \PrestaShopLogger::addLog('Sendy - RunScheduledTasks - Error: ' . $e->getMessage(), \PrestaShopLogger::LOG_SEVERITY_LEVEL_ERROR);
        }
    }
}
