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

use Sendy\PrestaShop\Action\HandleShipmentWebhook;
use Sendy\PrestaShop\Factory\ApiConnectionFactory;
use Sendy\PrestaShop\Installer\SystemUser;
use Sendy\PrestaShop\Legacy\DummyUrlGenerator;
use Sendy\PrestaShop\Legacy\SendyShipment;
use Sendy\PrestaShop\Repository\ConfigurationRepository;
use Sendy\PrestaShop\Repository\ShopConfigurationRepository;

/**
 * This file is part of the Sendy PrestaShop module - https://sendy.nl
 *
 * @author Sendy B.V.
 * @copyright Sendy B.V.
 * @license https://github.com/sendynl/prestashop-module/blob/master/LICENSE MIT
 *
 * @see https://github.com/sendynl/prestashop-module
 */
class SendyWebhookModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        try {
            $configurationRepository = new ConfigurationRepository(
                new PrestaShop\PrestaShop\Adapter\Configuration()
            );

            $this->ajax = true;

            $body = Tools::file_get_contents('php://input');
            $data = json_decode($body, true)['data'];

            if (!isset($data['event'], $data['id'], $data['resource'])) {
                $this->errors[] = 'Invalid request';
                http_response_code(400);
                $this->ajaxRender(json_encode(['errors' => $this->errors]));
            }

            if ($data['resource'] !== 'shipment') {
                return;
            }

            SystemUser::ensureInstalled();
            Context::getContext()->employee = new Employee($configurationRepository->getSendySystemUserId());

            $shipment = SendyShipment::getByUuid($data['id']);

            if ($shipment) {
                $shopContext = new PrestaShop\PrestaShop\Adapter\Shop\Context();
                $configuration = new PrestaShop\PrestaShop\Adapter\Configuration();
                $shopConfigurationRepository = new ShopConfigurationRepository($configuration, $shopContext);
                $configurationRepository = new ConfigurationRepository($configuration);
                $handleShipmentWebhook = new HandleShipmentWebhook(
                    $shopConfigurationRepository,
                    new ApiConnectionFactory($configurationRepository, new DummyUrlGenerator()),
                    $shopContext
                );

                $handleShipmentWebhook->execute($shipment, $data['event']);
            }
        } catch (Throwable $e) {
            http_response_code(500);
            throw $e;
        }
    }
}
