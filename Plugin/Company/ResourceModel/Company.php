<?php
/**
 * Copyright © 2018-2021 Hokodo. All Rights Reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Hokodo\BnplCommerce\Plugin\Company\ResourceModel;

use Hokodo\BNPL\Api\Data\CompanyInterface as ApiCompany;
use Hokodo\BnplCommerce\Api\CompanyRepositoryInterface;
use Hokodo\BnplCommerce\Api\Data\Company\CreditInterface;
use Hokodo\BnplCommerce\Api\Data\Company\CreditLimitInterface;
use Hokodo\BnplCommerce\Api\Data\Gateway\CompanyCreditRequestInterface;
use Hokodo\BnplCommerce\Api\Data\Gateway\CompanyCreditRequestInterfaceFactory;
use Hokodo\BnplCommerce\Api\Data\Gateway\CompanySearchRequestInterface;
use Hokodo\BnplCommerce\Api\Data\Gateway\CompanySearchRequestInterfaceFactory;
use Hokodo\BnplCommerce\Gateway\Service\Company as Gateway;
use Magento\Company\Model\Company as MagentoCompanyModel;
use Magento\Company\Model\ResourceModel\Company as MagentoCompanyResource;
use Magento\Framework\Exception\NotFoundException;
use Magento\Payment\Gateway\Command\CommandException;
use Psr\Log\LoggerInterface;

class Company
{
    public const DEFAULT_REG_NUMBER_ATTRIBUTE_CODE = 'vat_tax_id';

    /**
     * @var string
     */
    private string $regNumberAttributeCode;

    /**
     * @var CompanyRepositoryInterface
     */
    private CompanyRepositoryInterface $companyRepository;

    /**
     * @var Gateway
     */
    private Gateway $gateway;

    /**
     * @var CompanySearchRequestInterfaceFactory
     */
    private CompanySearchRequestInterfaceFactory $companySearchRequestFactory;

    /**
     * @var CompanyCreditRequestInterfaceFactory
     */
    private CompanyCreditRequestInterfaceFactory $companyCreditRequestFactory;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Company constructor.
     *
     * @param CompanyRepositoryInterface           $companyRepository
     * @param Gateway                              $gateway
     * @param CompanySearchRequestInterfaceFactory $companySearchRequestFactory
     * @param CompanyCreditRequestInterfaceFactory $companyCreditRequestFactory
     * @param LoggerInterface                      $logger
     * @param string|null                          $regNumberAttributeCode
     */
    public function __construct(
        CompanyRepositoryInterface $companyRepository,
        Gateway $gateway,
        CompanySearchRequestInterfaceFactory $companySearchRequestFactory,
        CompanyCreditRequestInterfaceFactory $companyCreditRequestFactory,
        LoggerInterface $logger,
        ?string $regNumberAttributeCode = null
    ) {
        $this->companyRepository = $companyRepository;
        $this->gateway = $gateway;
        $this->companySearchRequestFactory = $companySearchRequestFactory;
        $this->companyCreditRequestFactory = $companyCreditRequestFactory;
        $this->logger = $logger;
        $this->regNumberAttributeCode = $regNumberAttributeCode ?: self::DEFAULT_REG_NUMBER_ATTRIBUTE_CODE;
    }

    /**
     * Match Hokodo company after company is saved.
     *
     * @param MagentoCompanyResource $subject
     * @param MagentoCompanyResource $result
     * @param MagentoCompanyModel    $company
     *
     * @return MagentoCompanyResource
     */
    public function afterSave(
        MagentoCompanyResource $subject,
        MagentoCompanyResource $result,
        MagentoCompanyModel $company
    ) {
        $hokodoCompany = $this->companyRepository->getByEntityId((int) $company->getEntityId());
        if (!$hokodoCompany->getId() && ($apiCompany = $this->getHokodoApiCompany($company))) {
            $hokodoCompany
                ->setEntityId((int) $company->getEntityId())
                ->setCompanyId($apiCompany->getId())
                ->setCreditLimit($this->getCompanyCreditLimit($apiCompany->getId()));
            $this->companyRepository->save($hokodoCompany);
        }

        return $result;
    }

    /**
     * Get Hokodo API company.
     *
     * @param MagentoCompanyModel $company
     *
     * @return ApiCompany|null
     */
    private function getHokodoApiCompany(MagentoCompanyModel $company): ?ApiCompany
    {
        /** @var CompanySearchRequestInterface $searchRequest */
        $searchRequest = $this->companySearchRequestFactory->create();
        $searchRequest
            ->setCountry($company->getCountryId())
            ->setRegNumber($company->getData($this->regNumberAttributeCode));
        try {
            if ($list = $this->gateway->search($searchRequest)->getList()) {
                return reset($list);
            }
        } catch (NotFoundException|CommandException $e) {
            $data = [
                'message' => __('Can not find company. %1', $e->getMessage()),
            ];
            $data = array_merge($data, $company->getData());
            $this->logger->warning(__METHOD__, $data);
        }

        $this->logger->debug(__METHOD__, $company->getData());
        return null;
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
