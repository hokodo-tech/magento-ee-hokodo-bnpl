<?php
/**
 * Copyright © 2018-2021 Hokodo. All Rights Reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Hokodo\BnplCommerce\Api;

use Hokodo\BNPL\Api\HokodoEntityRepositoryInterface;
use Hokodo\BnplCommerce\Api\Data\CompanyInterface;

interface CompanyRepositoryInterface extends HokodoEntityRepositoryInterface
{
    /**
     * Save company to db.
     *
     * @param \Hokodo\BnplCommerce\Api\Data\CompanyInterface $company
     *
     * @return CompanyInterface
     */
    public function save(CompanyInterface $company): CompanyInterface;

    /**
     * Get Company by entity id.
     *
     * @param int $entityId
     *
     * @return \Hokodo\BnplCommerce\Api\Data\CompanyInterface
     */
    public function getByEntityId(int $entityId): CompanyInterface;
}
