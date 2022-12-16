<?php
/**
 * Copyright © 2018-2021 Hokodo. All Rights Reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Hokodo\BnplCommerce\Model;

use Hokodo\BNPL\Gateway\Config\Config as MainConfig;

class Config extends MainConfig
{
    public const DATA_SOURCE = 'data_source';
    public const DATA_SOURCE_CUSTOMER = 'customer';
    public const DATA_SOURCE_COMPANY = 'company';

    /**
     * Returns the current data source for company id.
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getEnvironment(int $storeId = null): string
    {
        return $this->getValue(self::DATA_SOURCE, $storeId);
    }
}