<?php
/**
 * Copyright © 2018-2021 Hokodo. All Rights Reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Hokodo\BnplCommerce\Model;

use Hokodo\BNPL\Api\HokodoEntityResolverInterface;
use Hokodo\Bnpl\Gateway\Config\Config;

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
        $entityLevel = $this->config->getEntityLevel();
        if ($entityLevel === Config::HOKODO_ENTITY_FOR_SAVE_COMPANY_LEVEL_IN_COMPANY) {
            return Config::HOKODO_ENTITY_FOR_SAVE_COMPANY_LEVEL_IN_COMPANY;
        }
        return Config::HOKODO_ENTITY_FOR_SAVE_COMPANY_LEVEL_IN_CUSTOMER;
    }
}
