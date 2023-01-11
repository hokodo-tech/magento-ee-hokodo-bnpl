<?php
declare(strict_types=1);

namespace Hokodo\BnplCommerce\Block\Adminhtml\Company\Edit;

use Hokodo\BnplCommerce\Api\CompanyRepositoryInterface;
use Hokodo\BnplCommerce\Api\Data\Company\CreditLimitInterface;
use Hokodo\BnplCommerce\Api\Data\CompanyInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class CreditBalance extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'company/edit/credit_balance.phtml';

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceFormatter;

    /**
     * @var CompanyInterface
     */
    private $company;

    /**
     * @var CreditLimitInterface
     */
    private $credit;

    /**
     * @var CompanyRepositoryInterface
     */
    private CompanyRepositoryInterface $companyRepository;

    /**
     * CreditBalance constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CompanyCredit\Api\Data\CreditLimitInterface $creditLimit
     * @param \Magento\CompanyCredit\Api\CreditDataProviderInterface $creditDataProvider
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceFormatter
     * @param \Magento\CompanyCredit\Model\WebsiteCurrency $websiteCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceFormatter,
        CompanyRepositoryInterface $companyRepository,
        array $data = []
    ) {
        $this->priceFormatter = $priceFormatter;
        $this->companyRepository = $companyRepository;
        parent::__construct($context, $data);
    }

    public function isEligible(): bool
    {
        return (bool) $this->getCredit();
    }

    public function getAmount()
    {
        $amount = $this->getCredit() ? $this->getCredit()->getAmount() / 100 : 0;

        return $this->priceFormatter->format(
            $amount,
            false,
            0,
            null,
            $this->getCredit()->getCurrency()
        );

    }

    public function getAmountInUse()
    {
        $amount = $this->getCredit() ? $this->getCredit()->getAmountInUse() / 100 : 0;

        return $this->priceFormatter->format(
            $amount,
            false,
            0,
            null,
            $this->getCredit()->getCurrency()
        );

    }

    public function getAmountAvailable()
    {
        $amount = $this->getCredit() ? $this->getCredit()->getAmountAvailable() / 100 : 0;

        return $this->priceFormatter->format(
            $amount,
            false,
            0,
            null,
            $this->getCredit()->getCurrency()
        );

    }

    /**
     * Get credit object.
     *
     * @return CreditLimitInterface|null
     */
    public function getCredit(): ?CreditLimitInterface
    {
        return $this->getCompany()->getCreditLimit();
    }

    public function getCompany(): CompanyInterface
    {
        $companyId = $this->getRequest()->getParam('id');
        if ($companyId && empty($this->company)) {
            $this->company = $this->companyRepository->getByEntityId((int) $companyId);
        }

        return $this->company;
    }
}
