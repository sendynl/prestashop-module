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

namespace Sendy\PrestaShop\Actions;

use InvalidArgumentException;
use Sendy\Api\Exceptions\SendyException;
use Sendy\PrestaShop\Enums\ProcessingMethod;

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
        if ($newProcessingMethod === ProcessingMethod::Sendy) {
            $this->installWebhook->execute();
        } elseif ($newProcessingMethod === ProcessingMethod::PrestaShop) {
            $this->uninstallWebhook->execute();
        } else {
            throw new InvalidArgumentException('Invalid processing method: ' . $newProcessingMethod);
        }
    }
}
