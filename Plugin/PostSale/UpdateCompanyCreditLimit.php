<?php

declare(strict_types=1);

namespace Hokodo\BnplCommerce\Plugin\PostSale;

use Hokodo\BNPL\Gateway\Command\PostSale\CapturePayment;
use Hokodo\BNPL\Gateway\Command\PostSale\RefundPayment;
use Hokodo\BNPL\Gateway\Command\PostSale\VoidPayment;
use Hokodo\BnplCommerce\Api\CompanyCreditServiceInterface;
use Hokodo\BnplCommerce\Api\CompanyRepositoryInterface;
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
     * @var CompanyRepositoryInterface
     */
    private CompanyRepositoryInterface $companyRepository;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var CompanyCreditServiceInterface
     */
    private CompanyCreditServiceInterface $companyCreditService;

    /**
     * UpdateHokodoCompanyLimit constructor.
     *
     * @param CompanyManagementInterface    $companyManagement
     * @param CompanyRepositoryInterface    $companyRepository
     * @param LoggerInterface               $logger
     * @param CompanyCreditServiceInterface $companyCreditService
     */
    public function __construct(
        CompanyManagementInterface $companyManagement,
        CompanyRepositoryInterface $companyRepository,
        LoggerInterface $logger,
        CompanyCreditServiceInterface $companyCreditService
    ) {
        $this->companyManagement = $companyManagement;
        $this->companyRepository = $companyRepository;
        $this->logger = $logger;
        $this->companyCreditService = $companyCreditService;
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
        // Adding try/catch to avoid code failing on successful post sale event.
        try {
            /* @var OrderInterface $order */
            if (($customerId = $commandSubject['payment']->getOrder()->getCustomerId()) &&
                ($company = $this->companyManagement->getByCustomerId((int) $customerId))) {
                $hokodoCompany = $this->companyRepository->getByEntityId((int) $company->getId());
                if ($hokodoCompany->getEntityId()) {
                    $hokodoCompany->setCreditLimit(
                        $this->companyCreditService->getCreditLimit($hokodoCompany->getCompanyId())
                    );
                    $this->companyRepository->save($hokodoCompany);
                }
            }
        } catch (\Exception $e) {
            $data = [
                //@codingStandardsIgnoreLine
                'message' => 'Hokodo_BNPL: company credit update failed with error on ' . gettype($subject),
                'error' => $e->getMessage(),
            ];
            $this->logger->debug(__METHOD__, $data);
        }

        return $result;
    }
}
