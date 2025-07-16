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

namespace Sendy\PrestaShop\Hook;

use Order;
use Sendy;
use Sendy\PrestaShop\Form\CreateShipment\CreateShipmentFormHandler;
use Sendy\PrestaShop\Repository\PackageRepository;
use Sendy\PrestaShop\Repository\ShipmentRepository;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

final class DisplayAdminOrderSide
{
    private Environment $twig;
    private CreateShipmentFormHandler $createShipmentFormHandler;
    private ShipmentRepository $shipmentRepository;
    private PackageRepository $packageRepository;

    public function __construct(
        Environment $twig,
        CreateShipmentFormHandler $createShipmentFormHandler,
        ShipmentRepository $shipmentRepository,
        PackageRepository $packageRepository
    ) {
        $this->twig = $twig;
        $this->createShipmentFormHandler = $createShipmentFormHandler;
        $this->shipmentRepository = $shipmentRepository;
        $this->packageRepository = $packageRepository;
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
        $shipment = $this->shipmentRepository->findShipmentByOrderId($params['id_order']);
        $packages = [];

        if ($shipment) {
            $packages = $this->packageRepository->findPackagesByShipmentId($shipment->getId());
        }

        $form = $this->createShipmentFormHandler->getForm();
        $form->get('order_ids')->setData([$params['id_order']]);

        return $this->twig->render('@Modules/sendy/views/templates/admin/order_side.html.twig', [
            'order' => new Order($params['id_order']),
            'shipment' => $shipment,
            'packages' => $packages,
            'createShipmentFormView' => $form->createView(),
            'editShipmentUrl' => Sendy::EDIT_SHIPMENT_URL,
            'viewPackageUrl' => Sendy::VIEW_PACKAGE_URL,
        ]);
    }
}
