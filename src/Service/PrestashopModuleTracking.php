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

namespace Sendy\PrestaShop\Service;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PrestashopModuleTracking
{
    private const SEGMENT_WRITE_KEY = 'AgqeWVEItnROTHgQHvOKnAa3Bso7w0nj';

    /**
     * Send tracking event to Segment.
     *
     * @param \Module $module
     * @param string $eventName
     * @param array<string, mixed> $properties
     *
     * @return void
     */
    public static function track(\Module $module, string $eventName, array $properties = []): void
    {
        $baseProperties = [
            'shop_url' => defined('_PS_BASE_URL_SSL_') ? _PS_BASE_URL_SSL_ : (defined('_PS_BASE_URL_') ? _PS_BASE_URL_ : ''),
            'ps_version' => defined('_PS_VERSION_') ? _PS_VERSION_ : '',
            'php_version' => PHP_VERSION,
            'module_version' => $module->version,
        ];

        if (!empty($properties)) {
            $baseProperties['custom'] = $properties;
        }

        self::log($module, sprintf('Preparing "%s" tracking.', $eventName), $baseProperties);

        try {
            if (method_exists($module, 'getService')) {
                $serviceName = sprintf('%s.ps_accounts_facade', $module->name);
                $accountsFacade = $module->getService($serviceName);
                if (is_object($accountsFacade) && method_exists($accountsFacade, 'getPsAccountsService')) {
                    $psAccountsService = $accountsFacade->getPsAccountsService();
                    $baseProperties = array_merge($baseProperties, [
                        'user_id' => $psAccountsService->getUserUuid(),
                        'email' => $psAccountsService->getEmail(),
                        'shop_id' => $psAccountsService->getShopUuid(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            if (class_exists('PrestaShopLogger')) {
                \PrestaShopLogger::addLog($e->getMessage(), 3, null, (string) $module->name);
            }
        }

        try {
            if (class_exists('\\Segment')) {
                \Segment::init(self::SEGMENT_WRITE_KEY);
                \Segment::track([
                    'anonymousId' => $module->name,
                    'event' => $eventName,
                    'properties' => $baseProperties,
                ]);
            } elseif (class_exists('\\Segment\\Segment')) {
                \Segment\Segment::init(self::SEGMENT_WRITE_KEY);
                \Segment\Segment::track([
                    'anonymousId' => $module->name,
                    'event' => $eventName,
                    'properties' => $baseProperties,
                ]);
            } else {
                self::log($module, 'Segment library not available, aborting track call.');

                return;
            }

            self::log($module, sprintf('Track "%s" sent.', $eventName));
        } catch (\Throwable $e) {
            if (class_exists('PrestaShopLogger')) {
                \PrestaShopLogger::addLog($e->getMessage(), 3, null, (string) $module->name);
            }
        }
    }

    /** @param array<string, mixed> $context */
    protected static function log(\Module $module, string $message, array $context = []): void
    {
        if (!class_exists('PrestaShopLogger')) {
            return;
        }

        if (!defined('_PS_MODE_DEV_') || !_PS_MODE_DEV_) {
            return;
        }

        \PrestaShopLogger::addLog(
            sprintf('[Tracking][%s] %s | Context: %s', $module->name, $message, json_encode($context)),
            1,
            null,
            (string) $module->name
        );
    }
}
