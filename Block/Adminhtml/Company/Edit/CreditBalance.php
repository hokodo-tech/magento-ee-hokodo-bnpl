<?php
/**
 * Copyright © 2018-2023 Hokodo. All Rights Reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Hokodo\BnplCommerce\Block\Adminhtml\Company\Edit;

use Hokodo\BNPL\Api\Data\Company\CreditLimitInterface;
use Hokodo\BnplCommerce\Api\CompanyRepositoryInterface;
use Hokodo\BnplCommerce\Api\Data\CompanyInterface;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class CreditBalance extends Template
{
    /**
     * @var string
     */
    protected $_template = 'company/edit/credit_balance.phtml';

    /**
     * @var PriceCurrencyInterface
     */
    private $priceFormatter;

    /**
     * @var CompanyInterface
     */
    private $company;

    /**
     * @var CompanyRepositoryInterface
     */
    private CompanyRepositoryInterface $companyRepository;

    /**
     * CreditBalance constructor.
     *
     * @param Context                    $context
     * @param PriceCurrencyInterface     $priceFormatter
     * @param CompanyRepositoryInterface $companyRepository
     * @param array                      $data
     */
    public function __construct(
        Context $context,
        PriceCurrencyInterface $priceFormatter,
        CompanyRepositoryInterface $companyRepository,
        array $data = []
    ) {
        $this->priceFormatter = $priceFormatter;
        $this->companyRepository = $companyRepository;
        parent::__construct($context, $data);
    }

    /**
     * Is company eligible.
     *
     * @return bool
     */
    public function isEligible(): bool
    {
        return (bool) $this->getCredit();
    }

    /**
     * Get credit object.
     *
     * @return CreditLimitInterface|null
     */
    public function getCredit(): ?CreditLimitInterface
    {
        return $this->getCompany()?->getCreditLimit();
    }

    /**
     * Get hokodo company.
     *
     * @return CompanyInterface|null
     */
    public function getCompany(): ?CompanyInterface
    {
        $companyId = $this->getRequest()->getParam('id');
        if ($companyId && empty($this->company)) {
            $this->company = $this->companyRepository->getByEntityId((int) $companyId);
        }

        return $this->company;
    }

    /**
     * Get credit amount.
     *
     * @return string
     */
    public function getAmount(): string
    {
        return $this->getFormattedPrice($this->getCredit() ? $this->getCredit()->getAmount() / 100 : 0, 0);
    }

    /**
     * Get credit amount in use.
     *
     * @return string
     */
    public function getAmountInUse(): string
    {
        return $this->getFormattedPrice($this->getCredit() ? $this->getCredit()->getAmountInUse() / 100 : 0);
    }

    /**
     * Get credit limit available.
     *
     * @return string
     */
    public function getAmountAvailable(): string
    {
        return $this->getFormattedPrice($this->getCredit() ? $this->getCredit()->getAmountAvailable() / 100 : 0);
    }

    /**
     * Get formatted price for components.
     *
     * @param float    $amount
     * @param int|null $precision
     *
     * @return string
     */
    private function getFormattedPrice(float $amount, int $precision = null): string
    {
        return $this->priceFormatter->format(
            $amount,
            false,
            $precision ?? 2,
            null,
            $this->getCredit()->getCurrency()
        );
    }
}
