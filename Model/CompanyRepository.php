<?php

declare(strict_types=1);

namespace Hokodo\BnplCommerce\Model;

use Hokodo\BnplCommerce\Api\CompanyRepositoryInterface;
use Hokodo\BnplCommerce\Api\Data\CompanyInterface;
use Hokodo\BnplCommerce\Api\Data\CompanyInterfaceFactory;
use Hokodo\BnplCommerce\Model\ResourceModel\Company as CompanyResource;
use Magento\Company\Api\CompanyManagementInterface;
use Magento\Framework\Api\DataObjectHelper;

class CompanyRepository implements CompanyRepositoryInterface
{
    /**
     * @var DataObjectHelper
     */
    private DataObjectHelper $dataObjectHelper;

    /**
     * @var CompanyFactory
     */
    private CompanyFactory $companyFactory;

    /**
     * @var CompanyResource
     */
    private CompanyResource $companyResource;

    /**
     * @var CompanyInterfaceFactory
     */
    private CompanyInterfaceFactory $companyInterfaceFactory;

    /**
     * @var CompanyManagementInterface
     */
    private CompanyManagementInterface $companyManagement;

    /**
     * CompanyRepository constructor.
     *
     * @param DataObjectHelper           $dataObjectHelper
     * @param CompanyFactory             $companyFactory
     * @param CompanyResource            $companyResource
     * @param CompanyInterfaceFactory    $companyInterfaceFactory
     * @param CompanyManagementInterface $companyManagement
     */
    public function __construct(
        DataObjectHelper $dataObjectHelper,
        CompanyFactory $companyFactory,
        CompanyResource $companyResource,
        CompanyInterfaceFactory $companyInterfaceFactory,
        CompanyManagementInterface $companyManagement
    ) {
        $this->dataObjectHelper = $dataObjectHelper;
        $this->companyFactory = $companyFactory;
        $this->companyResource = $companyResource;
        $this->companyInterfaceFactory = $companyInterfaceFactory;
        $this->companyManagement = $companyManagement;
    }

    /**
     * @inheritDoc
     */
    public function getByEntityId(int $entityId): CompanyInterface
    {
        $companyModel = $this->companyFactory->create();
        $companyDO = $this->companyInterfaceFactory->create();

        $this->companyResource->load($companyModel, $entityId, CompanyInterface::ENTITY_ID);
        $this->dataObjectHelper->populateWithArray($companyDO, $companyModel->getData(), CompanyInterface::class);

        return $companyDO;
    }

    /**
     * Get Hokodo Company Instance By Customer Id.
     *
     * @param int $entityId
     *
     * @return CompanyInterface
     */
    public function getById(int $entityId): CompanyInterface
    {
        $magentoCompanyId = 0;
        /** @var \Magento\Company\Api\Data\CompanyInterface $company */
        $company = $this->companyManagement->getByCustomerId($entityId);
        if ($company) {
            $magentoCompanyId = $company->getId();
        }
        $companyModel = $this->companyFactory->create();
        $companyDO = $this->companyInterfaceFactory->create();
        $this->companyResource->load($companyModel, $magentoCompanyId, CompanyInterface::ENTITY_ID);
        $this->dataObjectHelper->populateWithArray($companyDO, $companyModel->getData(), CompanyInterface::class);

        return $companyDO;
    }

    /**
     * @inheritDoc
     */
    public function save(CompanyInterface $company): CompanyInterface
    {
        $companyModel = $this->companyFactory->create();
        $this->companyResource->save($companyModel->setData($company->getData()));
        return $companyModel;
    }
}
