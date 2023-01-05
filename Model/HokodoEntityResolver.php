<?php
/**
 * Copyright © 2018-2021 Hokodo. All Rights Reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Hokodo\BnplCommerce\Model;

use Hokodo\BNPL\Api\HokodoEntityResolverInterface;
use Hokodo\Bnpl\Gateway\Config\Config;
use Hokodo\BnplCommerce\Model\Config\Source\EntityLevelForSave;

class HokodoEntityResolver implements HokodoEntityResolverInterface
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Get Entity Type.
     *
     * @return string
     */
    public function getEntityType(): string
    {
        return $this->config->getEntityLevel() ?: EntityLevelForSave::COMPANY;
    }
}
