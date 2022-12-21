<?php

namespace Hokodo\BnplCommerce\Api;

use Hokodo\BnplCommerce\Api\Data\CompanyInterface;

interface CompanyRepositoryInterface
{
    /**
     * Get Company by entity id.
     *
     * @param int $entityId
     *
     * @return \Hokodo\BnplCommerce\Api\Data\CompanyInterface
     */
    public function getByEntityId(int $entityId): CompanyInterface;

    /**
     * Save company to db.
     *
     * @param \Hokodo\BnplCommerce\Api\Data\CompanyInterface $company
     *
     * @return void
     */
    public function save(CompanyInterface $company): void;
}
