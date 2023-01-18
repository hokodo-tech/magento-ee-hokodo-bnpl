<?php
/**
 * Copyright © 2018-2021 Hokodo. All Rights Reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Hokodo\BnplCommerce\Model;

use Hokodo\BnplCommerce\Api\CompanyRepositoryInterface;
use Hokodo\BnplCommerce\Api\Data\CompanyInterface;
use Hokodo\BnplCommerce\Api\Data\CompanyInterfaceFactory;
use Hokodo\BnplCommerce\Model\Company as CompanyModel;
use Hokodo\BnplCommerce\Model\ResourceModel\Company as CompanyResource;
use Magento\Company\Api\CompanyManagementInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface as Logger;

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
     * @var Logger
     */
    private Logger $logger;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * CompanyRepository constructor.
     *
     * @param DataObjectHelper           $dataObjectHelper
     * @param CompanyFactory             $companyFactory
     * @param CompanyResource            $companyResource
     * @param CompanyInterfaceFactory    $companyInterfaceFactory
     * @param CompanyManagementInterface $companyManagement
     * @param Logger                     $logger
     * @param SerializerInterface        $serializer
     */
    public function __construct(
        DataObjectHelper $dataObjectHelper,
        CompanyFactory $companyFactory,
        CompanyResource $companyResource,
        CompanyInterfaceFactory $companyInterfaceFactory,
        CompanyManagementInterface $companyManagement,
        Logger $logger,
        SerializerInterface $serializer
    ) {
        $this->dataObjectHelper = $dataObjectHelper;
        $this->companyFactory = $companyFactory;
        $this->companyResource = $companyResource;
        $this->companyInterfaceFactory = $companyInterfaceFactory;
        $this->companyManagement = $companyManagement;
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public function getByEntityId(int $entityId): CompanyInterface
    {
        $companyModel = $this->companyFactory->create();
        $this->companyResource->load($companyModel, $entityId, CompanyInterface::ENTITY_ID);

        return $this->populateDataObject($companyModel);
    }

    /**
     * Get Hokodo Company Instance By Customer Id.
     *
     * @param int $customerId
     *
     * @return CompanyInterface
     */
    public function getByCustomerId(int $customerId): CompanyInterface
    {
        /** @var \Magento\Company\Api\Data\CompanyInterface $company */
        $company = $this->companyManagement->getByCustomerId($customerId);
        $companyModel = $this->companyFactory->create();
        if ($company) {
            $this->companyResource->load($companyModel, $company->getId(), CompanyInterface::ENTITY_ID);
        }

        return $this->populateDataObject($companyModel);
    }

    /**
     * Populate data model object from model data.
     *
     * @param CompanyModel $companyModel
     *
     * @return CompanyInterface
     */
    public function populateDataObject(CompanyModel $companyModel): CompanyInterface
    {
        $companyDO = $this->companyInterfaceFactory->create();
        if ($creditLimitJson = $companyModel->getData(CompanyInterface::CREDIT_LIMIT)) {
            $companyModel->setData(CompanyInterface::CREDIT_LIMIT, $this->serializer->unserialize($creditLimitJson));
        }
        $this->dataObjectHelper->populateWithArray($companyDO, $companyModel->getData(), CompanyInterface::class);

        return $companyDO;
    }

    /**
     * @inheritDoc
     */
    public function save(CompanyInterface $company): void
    {
        $companyModel = $this->companyFactory->create();
        if ($entityId = $company->getEntityId()) {
            $this->companyResource->load($companyModel, $entityId);
        }
        $companyModel->setData($company->getData());
        if ($creditLimit = $company->getCreditLimit()) {
            $companyModel->setData(CompanyInterface::CREDIT_LIMIT, $creditLimit->toJson());
        }

        $this->companyResource->save($companyModel);
    }
}
