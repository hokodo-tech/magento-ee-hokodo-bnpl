<?php
declare(strict_types = 1);

namespace Hokodo\BnplCommerce\Gateway\Service;

use Magento\Payment\Gateway\Command\ResultInterface;
use Hokodo\BNPL\Gateway\Service\AbstractService;
use Hokodo\BnplCommerce\Api\Data\Gateway\CompanySearchRequestInterface;

class Company extends AbstractService
{
    public function search(CompanySearchRequestInterface $request): ResultInterface
    {
        return $this->commandPool->get('hokodo_company_search')->execute($request->__toArray());
    }
}
