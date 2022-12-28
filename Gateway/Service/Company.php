<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Hokodo\BnplCommerce\Gateway\Service;

use Hokodo\BNPL\Gateway\Service\AbstractService;
use Hokodo\BnplCommerce\Api\Data\Gateway\CompanySearchRequestInterface;
use Magento\Payment\Gateway\Command\ResultInterface;

class Company extends AbstractService
{
    /**
     * API search payment gateway command.
     *
     * @param \Hokodo\BnplCommerce\Api\Data\Gateway\CompanySearchRequestInterface $request
     *
     * @return \Magento\Payment\Gateway\Command\ResultInterface
     *
     * @throws \Magento\Framework\Exception\NotFoundException
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    public function search(CompanySearchRequestInterface $request): ResultInterface
    {
        return $this->commandPool->get('hokodo_company_search')->execute($request->__toArray());
    }
}
