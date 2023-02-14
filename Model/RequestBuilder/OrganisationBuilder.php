<?php

namespace Hokodo\BnplCommerce\Model\RequestBuilder;

use Hokodo\BNPL\Api\Data\Gateway\CreateOrganisationRequestInterface;
use Hokodo\BNPL\Api\Data\Gateway\CreateOrganisationRequestInterfaceFactory;
use Hokodo\BNPL\Api\HokodoEntityTypeResolverInterface;
use Hokodo\BnplCommerce\Model\Config\Source\EntityLevelForSave;
use Magento\Company\Api\CompanyManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class OrganisationBuilder extends \Hokodo\BNPL\Model\RequestBuilder\OrganisationBuilder
{
    /**
     * @var HokodoEntityTypeResolverInterface
     */
    private HokodoEntityTypeResolverInterface $hokodoEntityTypeResolver;

    /**
     * @var CreateOrganisationRequestInterfaceFactory
     */
    private CreateOrganisationRequestInterfaceFactory $createOrganisationGatewayRequestFactory;

    /**
     * @var CompanyManagementInterface
     */
    private CompanyManagementInterface $companyManagement;

    /**
     * @var CustomerRepositoryInterface
     */
    private CustomerRepositoryInterface $customerRepository;

    /**
     * OrganisationBuilder constructor.
     *
     * @param CreateOrganisationRequestInterfaceFactory $createOrganisationGatewayRequestFactory
     * @param StoreManagerInterface                     $storeManager
     * @param HokodoEntityTypeResolverInterface         $hokodoEntityTypeResolver
     * @param CompanyManagementInterface                $companyManagement
     * @param CustomerRepositoryInterface               $customerRepository
     */
    public function __construct(
        CreateOrganisationRequestInterfaceFactory $createOrganisationGatewayRequestFactory,
        StoreManagerInterface $storeManager,
        HokodoEntityTypeResolverInterface $hokodoEntityTypeResolver,
        CompanyManagementInterface $companyManagement,
        CustomerRepositoryInterface $customerRepository
    ) {
        parent::__construct(
            $createOrganisationGatewayRequestFactory,
            $storeManager
        );
        $this->hokodoEntityTypeResolver = $hokodoEntityTypeResolver;
        $this->createOrganisationGatewayRequestFactory = $createOrganisationGatewayRequestFactory;
        $this->companyManagement = $companyManagement;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Build hokodo organisation based on entity setup.
     *
     * @param string $companyId
     * @param string $userEmail
     *
     * @return CreateOrganisationRequestInterface
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function build(string $companyId, string $userEmail = ''): CreateOrganisationRequestInterface
    {
        if ($this->hokodoEntityTypeResolver->resolve() === EntityLevelForSave::COMPANY) {
            $gatewayRequest = $this->createOrganisationGatewayRequestFactory->create();
            try {
                $company = $this->companyManagement->getByCustomerId(
                    $this->customerRepository->get($userEmail)->getId()
                );
                return $gatewayRequest
                    ->setCompanyId($companyId)
                    ->setUniqueId($this->generateUniqueId($companyId . $company->getId()))
                    ->setRegistered(date('Y-m-d\TH:i:s\Z'));
                //@codingStandardsIgnoreStart
            } catch (\Exception $e) {
                //This catch is empty for now as we are just fallback here to guest checkout.
            }
            //@codingStandardsIgnoreEnd
        }

        return parent::build($companyId, $userEmail);
    }
}
