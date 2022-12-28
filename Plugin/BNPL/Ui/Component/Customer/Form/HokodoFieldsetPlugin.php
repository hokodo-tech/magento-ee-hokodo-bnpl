<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Hokodo\BnplCommerce\Plugin\BNPL\Ui\Component\Customer\Form;

use Hokodo\BNPL\Gateway\Config\Config;
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
        if ($this->config->getEntityLevel() === Config::HOKODO_ENTITY_FOR_SAVE_COMPANY_LEVEL_IN_COMPANY) {
            return false;
        }
        return $result;
    }
}
