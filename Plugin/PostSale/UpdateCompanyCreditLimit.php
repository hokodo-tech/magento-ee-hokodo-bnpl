<?php

declare(strict_types=1);

namespace Hokodo\BnplCommerce\Plugin\PostSale;

use Hokodo\BNPL\Gateway\Command\PostSale\CapturePayment;
use Hokodo\BNPL\Gateway\Command\PostSale\RefundPayment;
use Hokodo\BNPL\Gateway\Command\PostSale\VoidPayment;
use Hokodo\BnplCommerce\Api\CompanyRepositoryInterface;
use Hokodo\BnplCommerce\Api\Data\Company\CreditInterface;
use Hokodo\BnplCommerce\Api\Data\Company\CreditLimitInterface;
use Hokodo\BnplCommerce\Api\Data\Gateway\CompanyCreditRequestInterface;
use Hokodo\BnplCommerce\Api\Data\Gateway\CompanyCreditRequestInterfaceFactory;
use Hokodo\BnplCommerce\Gateway\Service\Company;
use Magento\Company\Api\CompanyManagementInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

class UpdateCompanyCreditLimit
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
     * Update company's credit limit after successful post-sale command.
     *
     * @param CapturePayment|RefundPayment|VoidPayment $subject
     * @param mixed                                    $result
     * @param array                                    $commandSubject
     *
     * @return mixed
     */
    public function afterExecute($subject, $result, array $commandSubject)
    {
        /* @var OrderInterface $order */
        if (($customerId = $commandSubject['payment']->getOrder()->getCustomerId()) &&
            ($company = $this->companyManagement->getByCustomerId($customerId))) {
            $hokodoCompany = $this->companyRepository->getByEntityId($company->getId());
            if ($hokodoCompany->getEntityId()) {
                $hokodoCompany->setCreditLimit($this->getCompanyCreditLimit($hokodoCompany->getCompanyId()));
                $this->companyRepository->save($hokodoCompany);
            }
        }

        return $result;
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
