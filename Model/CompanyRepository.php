<?php
/**
 * Copyright © 2018-2023 Hokodo. All Rights Reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Hokodo\BnplCommerce\Model;

use Hokodo\BnplCommerce\Api\CompanyRepositoryInterface;
use Hokodo\BnplCommerce\Api\Data\CompanyInterface;
use Hokodo\BnplCommerce\Api\Data\CompanyInterfaceFactory;
use Hokodo\BnplCommerce\Model\Company as CompanyModel;
use Hokodo\BnplCommerce\Model\ResourceModel\Company as CompanyResource;
use Hokodo\BnplCommerce\Model\ResourceModel\Company\CollectionFactory as HokodoCompanyCollectionFactory;
use Magento\Company\Api\CompanyManagementInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Api\SearchResultsFactory;
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
     * @var HokodoCompanyCollectionFactory
     */
    private HokodoCompanyCollectionFactory $hokodoCompanyCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private CollectionProcessorInterface $collectionProcessor;

    /**
     * @var SearchResultsFactory
     */
    private SearchResultsFactory $searchResultsFactory;

    /**
     * CompanyRepository constructor.
     *
     * @param DataObjectHelper               $dataObjectHelper
     * @param CompanyFactory                 $companyFactory
     * @param CompanyResource                $companyResource
     * @param HokodoCompanyCollectionFactory $hokodoCompanyCollectionFactory
     * @param CollectionProcessorInterface   $collectionProcessor
     * @param SearchResultsFactory           $searchResultsFactory
     * @param CompanyInterfaceFactory        $companyInterfaceFactory
     * @param CompanyManagementInterface     $companyManagement
     * @param Logger                         $logger
     * @param SerializerInterface            $serializer
     */
    public function __construct(
        DataObjectHelper $dataObjectHelper,
        CompanyFactory $companyFactory,
        CompanyResource $companyResource,
        HokodoCompanyCollectionFactory $hokodoCompanyCollectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        SearchResultsFactory $searchResultsFactory,
        CompanyInterfaceFactory $companyInterfaceFactory,
        CompanyManagementInterface $companyManagement,
        Logger $logger,
        SerializerInterface $serializer
    ) {
        $this->dataObjectHelper = $dataObjectHelper;
        $this->companyFactory = $companyFactory;
        $this->companyResource = $companyResource;
        $this->hokodoCompanyCollectionFactory = $hokodoCompanyCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
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

    /**
     * Get List.
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return SearchResults
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResults
    {
        $collection = $this->hokodoCompanyCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($this->populateCollectionWithArray($collection->getItems()));
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * Populate Collection with array.
     *
     * @param array $collectionItems
     *
     * @return array
     */
    private function populateCollectionWithArray(array $collectionItems): array
    {
        $items = [];
        foreach ($collectionItems as $item) {
            $companyDataModel = $this->companyFactory->create();
            $companyDataModel->setData($item->getData());
            $items[] = $this->populateDataObject($companyDataModel);
        }
        return $items;
    }
}
