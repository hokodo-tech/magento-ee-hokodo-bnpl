<?php
declare(strict_types = 1);

namespace Hokodo\BnplCommerce\Plugin\Company\ResourceModel;

use Hokodo\BNPL\Api\Data\CompanyInterface as ApiCompany;
use Hokodo\BnplCommerce\Api\CompanyRepositoryInterface;
use Hokodo\BnplCommerce\Api\Data\Gateway\CompanySearchRequestInterface;
use Hokodo\BnplCommerce\Api\Data\Gateway\CompanySearchRequestInterfaceFactory;
use Hokodo\BnplCommerce\Gateway\Service\Company as Gateway;
use Magento\Company\Model\Company as MagentoCompanyModel;
use Magento\Company\Model\ResourceModel\Company as MagentoCompanyResource;

class Company
{
    public const DEFAULT_REG_NUMBER_ATTRIBUTE_CODE = 'vat_tax_id';

    private string $regNumberAttributeCode;

    /**
     * @var \Hokodo\BnplCommerce\Api\CompanyRepositoryInterface
     */
    private CompanyRepositoryInterface $companyRepository;

    /**
     * @var Gateway
     */
    private Gateway $gateway;

    /**
     * @var \Hokodo\BnplCommerce\Api\Data\Gateway\CompanySearchRequestInterfaceFactory
     */
    private CompanySearchRequestInterfaceFactory $companySearchRequestFactory;

    /**
     * Company constructor.
     *
     * @param \Hokodo\BnplCommerce\Api\CompanyRepositoryInterface $companyRepository
     * @param \Hokodo\BnplCommerce\Gateway\Service\Company $gateway
     * @param \Hokodo\BnplCommerce\Api\Data\Gateway\CompanySearchRequestInterfaceFactory $companySearchRequestFactory
     * @param string|null $regNumberAttributeCode
     */
    public function __construct(
        CompanyRepositoryInterface           $companyRepository,
        Gateway                              $gateway,
        CompanySearchRequestInterfaceFactory $companySearchRequestFactory,
        ?string                              $regNumberAttributeCode = null
    ) {
        $this->companyRepository = $companyRepository;
        $this->gateway = $gateway;
        $this->companySearchRequestFactory = $companySearchRequestFactory;
        $this->regNumberAttributeCode = $regNumberAttributeCode ?: self::DEFAULT_REG_NUMBER_ATTRIBUTE_CODE;
    }

    /**
     * Match Hokodo company after company is saved.
     *
     * @param \Magento\Company\Model\ResourceModel\Company $subject
     * @param \Magento\Company\Model\ResourceModel\Company $result
     * @param \Magento\Company\Model\Company $company
     *
     * @return \Magento\Company\Model\ResourceModel\Company
     */
    public function afterSave(MagentoCompanyResource $subject, MagentoCompanyResource $result, MagentoCompanyModel $company)
    {
        $hokodoCompany = $this->companyRepository->getByEntityId((int) $company->getEntityId());
        if (! $hokodoCompany->getId() && ($apiCompany = $this->getHokodoApiCompany($company))) {
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
     * @param \Magento\Company\Model\Company $company
     * @return \Hokodo\BNPL\Gateway\Command\Result\Company|null
     */
    private function getHokodoApiCompany(MagentoCompanyModel $company): ?ApiCompany
    {
        /** @var CompanySearchRequestInterface $searchRequest */
        $searchRequest = $this->companySearchRequestFactory->create();
        $searchRequest
            ->setCountry($company->getCountryId())
            ->setRegNumber($company->getData($this->regNumberAttributeCode));
        if ($list = $this->gateway->search($searchRequest)->getList()) {
            return reset($list);
        }

        //TODO add logger
        return null;
    }
}
