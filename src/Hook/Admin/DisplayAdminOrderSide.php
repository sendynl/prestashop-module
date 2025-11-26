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

namespace Sendy\PrestaShop\Hook\Admin;

use Order;
use Sendy;
use Sendy\PrestaShop\Form\CreateShipment\CreateShipmentFormHandler;
use Sendy\PrestaShop\Repository\ConfigurationRepository;
use Sendy\PrestaShop\Repository\PackageRepository;
use Sendy\PrestaShop\Repository\ShipmentRepository;
use Sendy\PrestaShop\Repository\ShopConfigurationRepository;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Displays the Sendy card on the side of the order page in the admin panel.
 *
 * @see https://devdocs.prestashop-project.org/9/modules/concepts/hooks/list-of-hooks/displayadminorderside/
 */
final class DisplayAdminOrderSide
{
    private Environment $twig;
    private CreateShipmentFormHandler $createShipmentFormHandler;
    private ShipmentRepository $shipmentRepository;
    private PackageRepository $packageRepository;
    private ShopConfigurationRepository $shopConfigurationRepository;
    private ConfigurationRepository $configurationRepository;

    public function __construct(
        Environment $twig,
        CreateShipmentFormHandler $createShipmentFormHandler,
        ShipmentRepository $shipmentRepository,
        PackageRepository $packageRepository,
        ShopConfigurationRepository $shopConfigurationRepository,
        ConfigurationRepository $configurationRepository
    ) {
        $this->twig = $twig;
        $this->createShipmentFormHandler = $createShipmentFormHandler;
        $this->shipmentRepository = $shipmentRepository;
        $this->packageRepository = $packageRepository;
        $this->shopConfigurationRepository = $shopConfigurationRepository;
        $this->configurationRepository = $configurationRepository;
    }

    /**
     * @param array{
     *     _ps_version: string,
     *     request: Request,
     *     route: string,
     *     id_order: int,
     *     cookie: \Cookie,
     *     cart: \Cart,
     *     altern: int,
     * } $params
     */
    public function __invoke(array $params): string
    {
        // Don't display the form if the user is not logged in.
        if ($this->configurationRepository->getAccessToken() === null) {
            return '';
        }

        $shipment = $this->shipmentRepository->findShipmentByOrderId($params['id_order']);
        $order = new \Order($params['id_order']);
        $packages = [];

        if ($shipment) {
            $packages = $this->packageRepository->findPackagesByShipmentId($shipment->getId());
        }

        $form = $this->createShipmentFormHandler->getForm();
        $form->get('order_ids')->setData([$params['id_order']]);
        $form->get('shop_id')->setData(
            $this->shopConfigurationRepository->getDefaultShop((int) $order->id_shop)
        );

        return $this->twig->render('@Modules/sendynl/views/templates/admin/order_side.html.twig', [
            'order' => $order,
            'shipment' => $shipment,
            'packages' => $packages,
            'createShipmentFormView' => $form->createView(),
            'editShipmentUrl' => \Sendynl::EDIT_SHIPMENT_URL,
            'viewPackageUrl' => \Sendynl::VIEW_PACKAGE_URL,
        ]);
    }
}
