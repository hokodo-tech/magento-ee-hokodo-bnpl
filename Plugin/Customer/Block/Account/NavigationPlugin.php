<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Hokodo\BnplCommerce\Plugin\Customer\Block\Account;

use Hokodo\BNPL\Gateway\Config\Config;
use Magento\Customer\Block\Account\Navigation;

class NavigationPlugin
{
    public const PATHS_TO_REMOVE = [
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
     * @param array $result
     *
     * @return array
     */
    public function afterGetLinks(Navigation $subject, $result): array
    {
        if ($this->config->getEntityLevel() === Config::HOKODO_ENTITY_FOR_SAVE_COMPANY_LEVEL_IN_COMPANY) {
            foreach ($result as $key => $link) {
                if (!$link->getData('path')) {
                    continue;
                }

                if (in_array($link->getData('path'), self::PATHS_TO_REMOVE)) {
                    unset($result[$key]);
                }
            }
        }
        return $result;
    }
}
