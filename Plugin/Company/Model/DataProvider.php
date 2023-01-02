<?php
/**
 * Copyright © 2018-2021 Hokodo. All Rights Reserved.
 * See LICENSE for license details.
 */

namespace Hokodo\BnplCommerce\Plugin\Company\Model;

use Hokodo\BnplCommerce\Api\CompanyRepositoryInterface;
use Magento\Company\Model\Company\DataProvider as CompanyDataProvider;
use Magento\Framework\UrlInterface;

/**
 * DataProvider for CompanyCredit form on a company edit page.
 */
class DataProvider
{
    /**
     * @var \Hokodo\BnplCommerce\Api\CompanyRepositoryInterface
     */
    private CompanyRepositoryInterface $companyRepository;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * DataProvider constructor.
     *
     * @param \Hokodo\BnplCommerce\Api\CompanyRepositoryInterface $companyRepository
     * @param \Magento\Framework\UrlInterface                     $urlBuilder
     */
    public function __construct(
        CompanyRepositoryInterface $companyRepository,
        UrlInterface $urlBuilder
    ) {
        $this->companyRepository = $companyRepository;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * After getCompanyResultData.
     *
     * @param CompanyDataProvider $subject
     * @param array               $result
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCompanyResultData(CompanyDataProvider $subject, array $result)
    {
        $hokodoCompanyId = '';
        if ($entityId = $result['id']) {
            $company = $this->companyRepository->getByEntityId((int) $entityId);
            if ($companyId = $company->getCompanyId()) {
                $hokodoCompanyId = $companyId;
            }
        }
        $result['hokodo'] = [
            'company_id' => $hokodoCompanyId ?: '',
            'submit_url' => $this->urlBuilder->getUrl('hokodo_commerce/company/savecompanyid'),
        ];
        return $result;
    }
}
