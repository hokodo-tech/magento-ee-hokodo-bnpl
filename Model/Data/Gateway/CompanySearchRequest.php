<?php

declare(strict_types=1);

namespace Hokodo\BnplCommerce\Model\Data\Gateway;

use Hokodo\BnplCommerce\Api\Data\Gateway\CompanySearchRequestInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class CompanySearchRequest extends AbstractSimpleObject implements CompanySearchRequestInterface
{
    /**
     * @inheritdoc
     */
    public function setRegNumber(string $regNumber): self
    {
        $this->setData(self::REGISTRATION_NUMBER, $regNumber);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCountry(string $country): self
    {
        $this->setData(self::COUNTRY, $country);
        return $this;
    }
}
