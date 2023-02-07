<?php
/**
 * Copyright © 2018-2023 Hokodo. All Rights Reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Hokodo\BnplCommerce\Plugin\Customer\Block\Account;

use Hokodo\BNPL\Gateway\Config\Config;
use Hokodo\BnplCommerce\Model\Config\Source\EntityLevelForSave;
use Magento\Customer\Block\Account\Navigation;

class NavigationPlugin
{
    public const PATH_TO_REMOVE = [
        'hokodo_bnpl/customer',
    ];

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
     * Hide My Company Tab in Customer account if company stored in Hokodo Company instance.
     *
     * @param Navigation $subject
     * @param array      $result
     *
     * @return array
     */
    public function afterGetLinks(Navigation $subject, $result): array
    {
        if ($this->config->getEntityLevel() === EntityLevelForSave::COMPANY) {
            foreach ($result as $key => $link) {
                if (!$link->getData('path')) {
                    continue;
                }

                if (in_array($link->getData('path'), self::PATH_TO_REMOVE)) {
                    unset($result[$key]);
                }
            }
        }
        return $result;
    }
}
