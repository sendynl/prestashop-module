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

namespace Sendy\PrestaShop\Action;

use InvalidArgumentException;
use Sendy\Api\Exceptions\SendyException;
use Sendy\PrestaShop\Enum\ProcessingMethod;

class ApplyProcessingMethodChange
{
    private InstallWebhook $installWebhook;
    private UninstallWebhook $uninstallWebhook;

    public function __construct(InstallWebhook $installWebhook, UninstallWebhook $uninstallWebhook)
    {
        $this->installWebhook = $installWebhook;
        $this->uninstallWebhook = $uninstallWebhook;
    }

    /**
     * @param ProcessingMethod::* $newProcessingMethod
     *
     * @throws SendyException
     */
    public function execute(string $newProcessingMethod): void
    {
        // TODO install webhook when the first shop sets the processing method to Sendy,
        // and uninstall it when the last shop sets it to prestashop
        if ($newProcessingMethod === ProcessingMethod::Sendy) {
            $this->installWebhook->execute();
        } elseif ($newProcessingMethod === ProcessingMethod::PrestaShop) {
            $this->uninstallWebhook->execute();
        } else {
            throw new InvalidArgumentException('Invalid processing method: ' . $newProcessingMethod);
        }
    }
}
