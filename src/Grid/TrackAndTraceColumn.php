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

namespace Sendy\PrestaShop\Grid;

use PrestaShop\PrestaShop\Core\Grid\Column\AbstractColumn;
use Symfony\Component\OptionsResolver\OptionsResolver;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @see views/PrestaShop/Admin/Common/Grid/Columns/Content/sendy_track_and_trace.html.twig
 */
class TrackAndTraceColumn extends AbstractColumn
{
    public function getType(): string
    {
        return 'sendy_track_and_trace';
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'sendy_edit_shipment_url' => \Sendy::EDIT_SHIPMENT_URL,
            'sendy_view_package_url' => \Sendy::VIEW_PACKAGE_URL,
        ]);
    }
}
