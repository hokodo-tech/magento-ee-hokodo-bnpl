<?php

namespace Hokodo\BnplCommerce\Api\Data\Gateway;

interface CompanySearchRequestInterface
{
    public const REGISTRATION_NUMBER = 'reg_number';
    public const COUNTRY = 'country';

    /**
     * Reg Number setter.
     *
     * @param string $regNumber
     *
     * @return $this
     */
    public function setRegNumber(string $regNumber): self;

    /**
     * Country setter.
     *
     * @param string $country
     *
     * @return $this
     */
    public function setCountry(string $country): self;
}
