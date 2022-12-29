<?php
/**
 * Copyright © 2018-2021 Hokodo. All Rights Reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Hokodo\BnplCommerce\Plugin\Company\ResourceModel;

use Hokodo\BNPL\Api\Data\CompanyInterface as ApiCompany;
use Hokodo\BnplCommerce\Api\CompanyRepositoryInterface;
use Hokodo\BnplCommerce\Api\Data\Gateway\CompanySearchRequestInterface;
use Hokodo\BnplCommerce\Api\Data\Gateway\CompanySearchRequestInterfaceFactory;
use Hokodo\BnplCommerce\Gateway\Service\Company as Gateway;
use Magento\Company\Model\Company as MagentoCompanyModel;
use Magento\Company\Model\ResourceModel\Company as MagentoCompanyResource;
use Magento\Framework\Exception\NotFoundException;
use Magento\Payment\Gateway\Command\CommandException;
use Psr\Log\LoggerInterface as Logger;

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
     * @var Logger
     */
    private Logger $logger;

    /**
     * Company constructor.
     *
     * @param CompanyRepositoryInterface           $companyRepository
     * @param Gateway                              $gateway
     * @param CompanySearchRequestInterfaceFactory $companySearchRequestFactory
     * @param Logger                               $logger
     * @param string|null                          $regNumberAttributeCode
     */
    public function __construct(
        CompanyRepositoryInterface $companyRepository,
        Gateway $gateway,
        CompanySearchRequestInterfaceFactory $companySearchRequestFactory,
        Logger $logger,
        ?string $regNumberAttributeCode = null
    ) {
        $this->companyRepository = $companyRepository;
        $this->gateway = $gateway;
        $this->companySearchRequestFactory = $companySearchRequestFactory;
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
                ->setCompanyId($apiCompany->getId());
            $this->companyRepository->save($hokodoCompany);
        }

        return $result;
    }

    /**
     * Get Hokodo API company.
     *
     * @param MagentoCompanyModel $company
     *
     * @return \Hokodo\BNPL\Gateway\Command\Result\Company|null
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
}
