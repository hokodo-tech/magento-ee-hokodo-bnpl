<?php
/**
 * Copyright © 2018-2023 Hokodo. All Rights Reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Hokodo\BnplCommerce\Plugin\BNPL\Model\Queue\Handler;

use Hokodo\BNPL\Model\Queue\Handler\CompanyImport;
use Magento\Company\Api\CompanyManagementInterface;

class CompanyImportPlugin
{
    /**
     * @var CompanyManagementInterface
     */
    private CompanyManagementInterface $companyManagement;

    /**
     * @param CompanyManagementInterface $companyManagement
     */
    public function __construct(
        CompanyManagementInterface $companyManagement
    ) {
        $this->companyManagement = $companyManagement;
    }

    /**
     * Set Entity Id.
     *
     * @param CompanyImport $subject
     * @param mixed         $hokodoEntity
     * @param int           $customerId
     *
     * @return array|null
     */
    public function beforeUpdateHokodoEntity(CompanyImport $subject, $hokodoEntity, int $customerId): ?array
    {
        if (!$hokodoEntity->getEntityId()) {
            $company = $this->companyManagement->getByCustomerId($customerId);
            $hokodoEntity->setEntityId((int) $company->getId());
        }

        return [$hokodoEntity, $customerId];
    }
}
