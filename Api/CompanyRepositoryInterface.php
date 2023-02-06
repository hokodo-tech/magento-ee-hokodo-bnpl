<?php
/**
 * Copyright © 2018-2023 Hokodo. All Rights Reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Hokodo\BnplCommerce\Api;

use Hokodo\BNPL\Api\HokodoEntityRepositoryInterface;
use Hokodo\BnplCommerce\Api\Data\CompanyInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResults;

interface CompanyRepositoryInterface extends HokodoEntityRepositoryInterface
{
    /**
     * Save company to db.
     *
     * @param \Hokodo\BnplCommerce\Api\Data\CompanyInterface $company
     *
     * @return CompanyInterface
     */
    public function save(CompanyInterface $company): void;

    /**
     * Get Company by entity id.
     *
     * @param int $entityId
     *
     * @return \Hokodo\BnplCommerce\Api\Data\CompanyInterface
     */
    public function getByEntityId(int $entityId): CompanyInterface;

    /**
     * Get List.
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return SearchResults
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResults;
}
