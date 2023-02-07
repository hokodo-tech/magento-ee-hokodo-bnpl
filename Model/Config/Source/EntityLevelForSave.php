<?php
/**
 * Copyright © 2018-2023 Hokodo. All Rights Reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Hokodo\BnplCommerce\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class EntityLevelForSave implements OptionSourceInterface
{
    public const COMPANY = 'company';

    /**
     * Get Options Array.
     *
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => \Hokodo\BNPL\Model\Config\Source\EntityLevelForSave::CUSTOMER,
                'label' => __('Customer'),
            ],
            [
                'value' => self::COMPANY,
                'label' => __('Company'),
            ],
        ];
    }
}
