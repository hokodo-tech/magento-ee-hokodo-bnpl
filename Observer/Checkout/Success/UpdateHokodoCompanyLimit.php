<?php
declare(strict_types=1);

namespace Hokodo\BnplCommerce\Observer\Checkout\Success;

use Hokodo\BNPL\Gateway\Config\Config;
use Hokodo\BnplCommerce\Api\CompanyRepositoryInterface;
use Hokodo\BnplCommerce\Api\Data\Company\CreditInterface;
use Hokodo\BnplCommerce\Api\Data\Company\CreditLimitInterface;
use Hokodo\BnplCommerce\Api\Data\Gateway\CompanyCreditRequestInterface;
use Hokodo\BnplCommerce\Api\Data\Gateway\CompanyCreditRequestInterfaceFactory;
use Hokodo\BnplCommerce\Gateway\Service\Company;
use Magento\Company\Api\CompanyManagementInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

class UpdateHokodoCompanyLimit implements ObserverInterface
{
    /**
     * @var CompanyManagementInterface
     */
    private CompanyManagementInterface $companyManagement;

    /**
     * @var Company
     */
    private Company $gateway;

    /**
     * @var CompanyRepositoryInterface
     */
    private CompanyRepositoryInterface $companyRepository;

    /**
     * @var CompanyCreditRequestInterfaceFactory
     */
    private CompanyCreditRequestInterfaceFactory $companyCreditRequestFactory;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * UpdateHokodoCompanyLimit constructor.
     *
     * @param CompanyManagementInterface           $companyManagement
     * @param Company                              $gateway
     * @param CompanyRepositoryInterface           $companyRepository
     * @param CompanyCreditRequestInterfaceFactory $companyCreditRequestFactory
     * @param LoggerInterface                      $logger
     */
    public function __construct(
        CompanyManagementInterface $companyManagement,
        Company $gateway,
        CompanyRepositoryInterface $companyRepository,
        CompanyCreditRequestInterfaceFactory $companyCreditRequestFactory,
        LoggerInterface $logger
    ) {
        $this->companyManagement = $companyManagement;
        $this->gateway = $gateway;
        $this->companyRepository = $companyRepository;
        $this->companyCreditRequestFactory = $companyCreditRequestFactory;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var OrderInterface $order */
        $order = $observer->getEvent()->getOrder();
        if (($customerId = $order->getCustomerId()) && $order->getPayment()->getMethod() === Config::CODE) {
            $company = $this->companyManagement->getByCustomerId($customerId);
            $hokodoCompany = $this->companyRepository->getByEntityId($company->getId());
            if ($hokodoCompany->getEntityId()) {
                $hokodoCompany->setCreditLimit($this->getCompanyCreditLimit($hokodoCompany->getCompanyId()));
                $this->companyRepository->save($hokodoCompany);
            }
        }
    }

    /**
     * Get company credit limit.
     *
     * @param string $companyId
     *
     * @return CreditLimitInterface|null
     */
    private function getCompanyCreditLimit(string $companyId): ?CreditLimitInterface
    {
        /** @var CompanyCreditRequestInterface $searchRequest */
        $searchRequest = $this->companyCreditRequestFactory->create();
        $searchRequest->setCompanyId($companyId);

        try {
            /** @var CreditInterface $companyCredit */
            $companyCredit = $this->gateway->getCredit($searchRequest)->getDataModel();
            if (!$companyCredit->getRejectionReason()) {
                return $companyCredit->getCreditLimit();
            }
        } catch (\Exception $e) {
            $data = [
                'message' => 'Hokodo_BNPL: company credit call failed with error.',
                'error' => $e->getMessage(),
            ];
            $this->logger->error(__METHOD__, $data);
        }

        return null;
    }
}
