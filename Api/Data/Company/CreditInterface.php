<?php

namespace Hokodo\BnplCommerce\Api\Data\Company;

interface CreditInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    public const COMPANY = 'company';
    public const STATUS = 'status';
    public const REJECTION_REASON = 'rejection_reason';
    public const CREDIT_LIMIT = 'credit_limit';

    /**
     * Company getter.
     *
     * @return string
     */
    public function getCompany(): string;

    /**
     * Company setter.
     *
     * @param string $company
     *
     * @return $this
     */
    public function setCompany(string $company): self;

    /**
     * Status getter.
     *
     * @return string
     */
    public function getStatus(): string;

    /**
     * Status setter.
     *
     * @param string $status
     *
     * @return $this
     */
    public function setStatus(string $status): self;

    /**
     * Rejection Reason getter.
     *
     * @return array|null
     */
    public function getRejectionReason(): ?array;

    /**
     * Rejection Reason setter.
     *
     * @param array|null $rejectionReason
     *
     * @return $this
     */
    public function setRejectionReason(?array $rejectionReason): self;

    /**
     * Credit Limit getter.
     *
     * @return \Hokodo\BnplCommerce\Api\Data\Company\CreditLimitInterface
     */
    public function getCreditLimit(): CreditLimitInterface;

    /**
     * Credit Limit setter.
     *
     * @param \Hokodo\BnplCommerce\Api\Data\Company\CreditLimitInterface $creditLimit
     *
     * @return $this
     */
    public function setCreditLimit(CreditLimitInterface $creditLimit): self;
}