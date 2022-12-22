<?php
/**
 * Copyright © 2018-2021 Hokodo. All Rights Reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Hokodo\BnplCommerce\Model\Source\EntityForSave;

use Hokodo\BNPL\Gateway\Config\Config;
use Magento\Framework\Data\OptionSourceInterface;

class EntityLevel implements OptionSourceInterface
{
    /**
     * Get Options Array
     *
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => Config::HOKODO_ENTITY_FOR_SAVE_COMPANY_LEVEL_IN_CUSTOMER,
                'label' => __('Customer'),
            ],
            [
                'value' => Config::HOKODO_ENTITY_FOR_SAVE_COMPANY_LEVEL_IN_COMPANY,
                'label' => __('Company'),
            ],
        ];
    }
}
