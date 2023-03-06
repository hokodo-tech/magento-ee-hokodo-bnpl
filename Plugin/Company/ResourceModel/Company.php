<?php
/**
 * Copyright © 2018-2023 Hokodo. All Rights Reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Hokodo\BnplCommerce\Plugin\Company\ResourceModel;

use Hokodo\BNPL\Api\CompanyCreditServiceInterface;
use Hokodo\BNPL\Api\Data\CompanyInterface as ApiCompany;
use Hokodo\BNPL\Api\Data\Gateway\CompanySearchRequestInterface;
use Hokodo\BNPL\Api\Data\Gateway\CompanySearchRequestInterfaceFactory;
use Hokodo\BNPL\Gateway\Service\Company as Gateway;
use Hokodo\BnplCommerce\Api\CompanyRepositoryInterface;
use Magento\Backend\Model\UrlInterface;
use Magento\Company\Model\Company as MagentoCompanyModel;
use Magento\Company\Model\ResourceModel\Company as MagentoCompanyResource;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Notification\NotifierInterface;
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
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var CompanyCreditServiceInterface
     */
    private CompanyCreditServiceInterface $companyCreditService;

    /**
     * @var NotifierInterface
     */
    private NotifierInterface $notifier;

    /**
     * @var UrlInterface
     */
    private UrlInterface $url;

    /**
     * Company constructor.
     *
     * @param CompanyRepositoryInterface           $companyRepository
     * @param Gateway                              $gateway
     * @param CompanySearchRequestInterfaceFactory $companySearchRequestFactory
     * @param LoggerInterface                      $logger
     * @param CompanyCreditServiceInterface        $companyCreditService
     * @param NotifierInterface                    $notifier
     * @param UrlInterface                         $url
     * @param string|null                          $regNumberAttributeCode
     */
    public function __construct(
        CompanyRepositoryInterface $companyRepository,
        Gateway $gateway,
        CompanySearchRequestInterfaceFactory $companySearchRequestFactory,
        LoggerInterface $logger,
        CompanyCreditServiceInterface $companyCreditService,
        NotifierInterface $notifier,
        UrlInterface $url,
        ?string $regNumberAttributeCode = null
    ) {
        $this->companyRepository = $companyRepository;
        $this->gateway = $gateway;
        $this->companySearchRequestFactory = $companySearchRequestFactory;
        $this->logger = $logger;
        $this->regNumberAttributeCode = $regNumberAttributeCode ?: self::DEFAULT_REG_NUMBER_ATTRIBUTE_CODE;
        $this->companyCreditService = $companyCreditService;
        $this->notifier = $notifier;
        $this->url = $url;
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
        if (!$hokodoCompany->getId() &&
            $company->getData($this->regNumberAttributeCode) &&
            ($apiCompany = $this->getHokodoApiCompany($company))) {
            $hokodoCompany
                ->setEntityId((int) $company->getEntityId())
                ->setCompanyId($apiCompany->getId())
                ->setCreditLimit($this->companyCreditService->getCreditLimit($apiCompany->getId()));
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
            } else {
                $this->notifier->addMajor(
                    __('Hokodo BNPL: auto company assignment failed.'),
                    __('Please review company %1 manually', $company->getCompanyName()),
                    $this->url->getUrl(
                        'company/index/edit',
                        ['_secure' => true, 'id' => $company->getId()]
                    )
                );
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
