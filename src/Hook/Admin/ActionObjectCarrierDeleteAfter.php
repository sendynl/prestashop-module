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

namespace Sendy\PrestaShop\Hook\Admin;

use Sendy\PrestaShop\Action\SynchronizeCarriers;

class ActionObjectCarrierDeleteAfter
{
    private SynchronizeCarriers $synchronizeCarriers;

    public function __construct(SynchronizeCarriers $synchronizeCarriers)
    {
        $this->synchronizeCarriers = $synchronizeCarriers;
    }

    /**
     * @param array{
     *     object: \Carrier,
     *     cookie: \Cookie,
     *     cart: null,
     *     altern: int,
     * } $params
     */
    public function __invoke(array $params): void
    {
        $this->synchronizeCarriers->execute();
    }
}
