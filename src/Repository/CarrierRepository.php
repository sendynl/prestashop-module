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

namespace Sendy\PrestaShop\Repository;

use Sendy\PrestaShop\Enum\Carrier as CarrierEnum;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CarrierRepository
{
    public function create(string $carrierType, string $delayDescription): int
    {
        $carrier = new \Carrier();
        $carrier->name = CarrierEnum::getDescription($carrierType);
        $carrier->is_module = true;
        $carrier->active = true;
        $carrier->need_range = true;
        $carrier->shipping_external = true;
        $carrier->range_behavior = false;
        $carrier->external_module_name = 'sendy';
        $carrier->shipping_method = 2;

        foreach (\Language::getLanguages() as $lang) {
            $carrier->delay[$lang['id_lang']] = $delayDescription;
        }

        if (!$carrier->add()) {
            throw new \Exception('Failed to create carrier');
        }

        $this->addGroups($carrier);
        $this->addRanges($carrier);
        $this->addZones($carrier);

        return (int) $carrier->id;
    }

    protected function addGroups(\Carrier $carrier): void
    {
        $groups_ids = [];
        $groups = \Group::getGroups(\Context::getContext()->language->id);
        foreach ($groups as $group) {
            $groups_ids[] = $group['id_group'];
        }

        $carrier->setGroups($groups_ids);
    }

    protected function addRanges(\Carrier $carrier): void
    {
        $range_price = new \RangePrice();
        $range_price->id_carrier = $carrier->id;
        $range_price->delimiter1 = 0.0;
        $range_price->delimiter2 = 10000.0;
        $range_price->add();

        $range_weight = new \RangeWeight();
        $range_weight->id_carrier = $carrier->id;
        $range_weight->delimiter1 = 0.0;
        $range_weight->delimiter2 = 10000.0;
        $range_weight->add();
    }

    protected function addZones(\Carrier $carrier): void
    {
        $zones = \Zone::getZones();

        foreach ($zones as $zone) {
            $carrier->addZone($zone['id_zone']);
        }
    }

    /**
     * @return list<array{
     * id_carrier: int,
     * id_reference: int,
     * name: string,
     * url: string,
     * active: int<0,1>,
     * deleted: int<0,1>,
     * shipping_handling: int,
     * range_behavior: int,
     * is_module: int<0,1>,
     * is_free: int<0,1>,
     * shipping_external: int<0,1>,
     * need_range: int<0,1>,
     * external_module_name: string,
     * shipping_method: int,
     * position: int,
     * max_width: int,
     * max_height: int,
     * max_depth: int,
     * max_weight: float|string,
     * grade: int,
     * delay: string,
     * }>
     */
    public function all(): array
    {
        return \Carrier::getCarriers(\Context::getContext()->language->id, false, false, false, null, \Carrier::ALL_CARRIERS);
    }
}
