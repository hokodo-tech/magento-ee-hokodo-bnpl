<?php
/**
 * Copyright © 2018-2023 Hokodo. All Rights Reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Hokodo\BnplCommerce\Model;

use Hokodo\BNPL\Api\HokodoEntityTypeResolverInterface;
use Hokodo\Bnpl\Gateway\Config\Config;
use Hokodo\BnplCommerce\Model\Config\Source\EntityLevelForSave;

class HokodoEntityTypeResolver implements HokodoEntityTypeResolverInterface
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
    public function resolve(): string
    {
        return $this->config->getEntityLevel() ?: EntityLevelForSave::COMPANY;
    }
}
