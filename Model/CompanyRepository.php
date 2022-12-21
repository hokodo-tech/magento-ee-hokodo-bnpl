<?php

declare(strict_types=1);

namespace Hokodo\BnplCommerce\Model;

use Hokodo\BnplCommerce\Api\CompanyRepositoryInterface;
use Hokodo\BnplCommerce\Api\Data\CompanyInterface;
use Hokodo\BnplCommerce\Api\Data\CompanyInterfaceFactory;
use Hokodo\BnplCommerce\Model\CompanyFactory;
use Hokodo\BnplCommerce\Model\ResourceModel\Company as CompanyResource;
use Magento\Framework\Api\DataObjectHelper;

class CompanyRepository implements CompanyRepositoryInterface
{
    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private DataObjectHelper $dataObjectHelper;

    /**
     * @var \Hokodo\BnplCommerce\Model\CompanyFactory
     */
    private CompanyFactory $companyFactory;

    /**
     * @var \Hokodo\BnplCommerce\Model\ResourceModel\Company
     */
    private CompanyResource $companyResource;

    /**
     * @var \Hokodo\BnplCommerce\Api\Data\CompanyInterfaceFactory
     */
    private CompanyInterfaceFactory $companyInterfaceFactory;

    /**
     * CompanyRepository constructor.
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Hokodo\BnplCommerce\Model\CompanyFactory $companyFactory
     * @param \Hokodo\BnplCommerce\Model\ResourceModel\Company $companyResource
     * @param \Hokodo\BnplCommerce\Api\Data\CompanyInterfaceFactory $companyInterfaceFactory
     */
    public function __construct(
        DataObjectHelper        $dataObjectHelper,
        CompanyFactory          $companyFactory,
        CompanyResource         $companyResource,
        CompanyInterfaceFactory $companyInterfaceFactory
    ) {
        $this->dataObjectHelper = $dataObjectHelper;
        $this->companyFactory = $companyFactory;
        $this->companyResource = $companyResource;
        $this->companyInterfaceFactory = $companyInterfaceFactory;
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
     * @inheritDoc
     */
    public function save(CompanyInterface $company): void
    {
        $companyModel = $this->companyFactory->create();
        $this->companyResource->save($companyModel->setData($company->getData()));
    }
}
