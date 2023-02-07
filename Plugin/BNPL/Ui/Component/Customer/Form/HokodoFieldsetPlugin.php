<?php
/**
 * Copyright © 2018-2023 Hokodo. All Rights Reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Hokodo\BnplCommerce\Plugin\BNPL\Ui\Component\Customer\Form;

use Hokodo\BNPL\Gateway\Config\Config;
use Hokodo\BNPL\Model\Config\Source\EntityLevelForSave;
use Hokodo\BNPL\Ui\Component\Customer\Form\HokodoFieldset;

class HokodoFieldsetPlugin
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
     * Hide Company (Hokodo) Tab in Customer account in Magento Backend if company stored in Hokodo Company instance.
     *
     * @param HokodoFieldset $subject
     * @param bool           $result
     *
     * @return bool
     */
    public function afterIsComponentVisible(HokodoFieldset $subject, $result): bool
    {
        return $this->config->getEntityLevel() === EntityLevelForSave::CUSTOMER;
    }
}
