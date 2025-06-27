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

namespace Sendy\PrestaShop\Grid;

use PrestaShop\PrestaShop\Core\Grid\Column\AbstractColumn;

/**
 * @see views/PrestaShop/Admin/Common/Grid/Columns/Content/sendy_track_and_trace.html.twig
 */
class TrackAndTraceColumn extends AbstractColumn
{
    public function getType()
    {
        return 'sendy_track_and_trace';
    }
}
