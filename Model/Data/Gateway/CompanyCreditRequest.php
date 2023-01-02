<?php

declare(strict_types=1);

namespace Hokodo\BnplCommerce\Model\Data\Gateway;

use Hokodo\BnplCommerce\Api\Data\Gateway\CompanyCreditRequestInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class CompanyCreditRequest extends AbstractSimpleObject implements CompanyCreditRequestInterface
{
    /**
     * @inheritDoc
     */
    public function setCurrency(string $currency): CompanyCreditRequestInterface
    {
        $this->setData(self::CURRENCY, $currency);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCompanyId(string $companyId): self
    {
        $this->setData(self::COMPANY_ID, $companyId);
        return $this;
    }
}
