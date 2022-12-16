<?php
/**
 * Copyright © 2018-2021 Hokodo. All Rights Reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Hokodo\BnplCommerce\Model\Adminhtml\Source;

use Hokodo\BnplCommerce\Model\Config;
use Magento\Framework\Data\OptionSourceInterface;

class DataSource implements OptionSourceInterface
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
                'value' => Config::DATA_SOURCE_CUSTOMER,
                'label' => __('Customer'),
            ],
            [
                'value' => Config::DATA_SOURCE_COMPANY,
                'label' => __('Company'),
            ],
        ];
    }
}
