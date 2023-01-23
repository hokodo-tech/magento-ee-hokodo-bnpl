<?php
/**
 * Copyright © 2018-2021 Hokodo. All Rights Reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Hokodo\BnplCommerce\Observer;

use Hokodo\BnplCommerce\Api\CompanyRepositoryInterface;
use Hokodo\BnplCommerce\Api\Data\CompanyInterface;
use Hokodo\BnplCommerce\Model\CompanyCreditService;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Observes the `sales_model_service_quote_submit_success` event.
 */
class OrderPlaceSuccessObserver implements ObserverInterface
{
    /**
     * @var CompanyCreditService
     */
    private CompanyCreditService $companyCreditService;

    /**
     * @var CompanyRepositoryInterface
     */
    private CompanyRepositoryInterface $companyRepository;

    /**
     * OrderPlaceSuccessObserver constructor.
     *
     * @param CompanyRepositoryInterface $companyRepository
     * @param CompanyCreditService       $companyCreditService
     */
    public function __construct(
        CompanyRepositoryInterface $companyRepository,
        CompanyCreditService $companyCreditService
    ) {
        $this->companyRepository = $companyRepository;
        $this->companyCreditService = $companyCreditService;
    }

    /**
     * Observer for sales_model_service_quote_submit_success.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var OrderInterface $order */
        $order = $observer->getEvent()->getOrder();
        if ($order->getPayment()->getMethod() === \Hokodo\BNPL\Gateway\Config\Config::CODE) {
            /** @var CompanyInterface $company */
            $company = $this->companyRepository->getByCustomerId((int) $order->getCustomerId());
            if ($companyId = $company->getCompanyId()) {
                $company->setCreditLimit(
                    $this->companyCreditService->getCreditLimit($companyId)
                );
                $this->companyRepository->save($company);
            }
        }
    }
}
