<?php
/**
 * Copyright © 2018-2021 Hokodo. All Rights Reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Hokodo\BnplCommerce\Controller\Adminhtml\Company;

use Hokodo\BNPL\Api\HokodoQuoteRepositoryInterface;
use Hokodo\BnplCommerce\Api\CompanyRepositoryInterface;
use Hokodo\BnplCommerce\Api\Data\Company\CreditInterface;
use Hokodo\BnplCommerce\Api\Data\Company\CreditLimitInterface;
use Hokodo\BnplCommerce\Api\Data\Gateway\CompanyCreditRequestInterface;
use Hokodo\BnplCommerce\Api\Data\Gateway\CompanyCreditRequestInterfaceFactory;
use Hokodo\BnplCommerce\Gateway\Service\Company;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Company\Api\CompanyRepositoryInterface as MagentoCompanyRepositoryInterface;
use Magento\CompanyGraphQl\Model\Company\Users as CompanyUsers;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\SessionCleanerInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class SaveCompanyId extends Action implements HttpPostActionInterface
{
    /**
     * @var CompanyRepositoryInterface
     */
    private CompanyRepositoryInterface $companyRepository;

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepository;

    /**
     * @var SessionCleanerInterface
     */
    private SessionCleanerInterface $sessionCleaner;

    /**
     * @var CompanyUsers
     */
    private CompanyUsers $companyUsers;

    /**
     * @var MagentoCompanyRepositoryInterface
     */
    private MagentoCompanyRepositoryInterface $magentoCompanyRepository;

    /**
     * @var HokodoQuoteRepositoryInterface
     */
    private HokodoQuoteRepositoryInterface $hokodoQuoteRepository;

    /**
     * @var CompanyCreditRequestInterfaceFactory
     */
    private CompanyCreditRequestInterfaceFactory $companyCreditRequestFactory;

    /**
     * @var Company
     */
    private Company $gateway;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * SaveCompanyId constructor.
     *
     * @param Context                              $context
     * @param CompanyRepositoryInterface           $companyRepository
     * @param CartRepositoryInterface              $cartRepository
     * @param SessionCleanerInterface              $sessionCleaner
     * @param CompanyUsers                         $companyUsers
     * @param MagentoCompanyRepositoryInterface    $magentoCompanyRepository
     * @param HokodoQuoteRepositoryInterface       $hokodoQuoteRepository
     * @param CompanyCreditRequestInterfaceFactory $companyCreditRequestFactory
     * @param Company                              $gateway
     * @param LoggerInterface                      $logger
     * @param StoreManagerInterface                $storeManager
     */
    public function __construct(
        Context $context,
        CompanyRepositoryInterface $companyRepository,
        CartRepositoryInterface $cartRepository,
        SessionCleanerInterface $sessionCleaner,
        CompanyUsers $companyUsers,
        MagentoCompanyRepositoryInterface $magentoCompanyRepository,
        HokodoQuoteRepositoryInterface $hokodoQuoteRepository,
        CompanyCreditRequestInterfaceFactory $companyCreditRequestFactory,
        Company $gateway,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->companyRepository = $companyRepository;
        $this->cartRepository = $cartRepository;
        $this->sessionCleaner = $sessionCleaner;
        $this->companyUsers = $companyUsers;
        $this->magentoCompanyRepository = $magentoCompanyRepository;
        $this->hokodoQuoteRepository = $hokodoQuoteRepository;
        $this->companyCreditRequestFactory = $companyCreditRequestFactory;
        $this->gateway = $gateway;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
    }

    /**
     * Execute action based on request and return result.
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $result = [
            'success' => false,
            'message' => __('Error, the company has not been updated.'),
        ];

        $entityId = (int) $this->getRequest()->getParam('entityId');
        $companyId = $this->getRequest()->getParam('companyId');

        if ($entityId && $companyId) {
            $hokodoCompany = $this->companyRepository->getByEntityId((int) $entityId);
            if (!$hokodoCompany->getId()) {
                $hokodoCompany->setEntityId($entityId);
            }
            $oldCompanyId = $hokodoCompany->getCompanyId();
            $hokodoCompany->setCompanyId($companyId);
            $hokodoCompany->setCreditLimit($this->getCompanyCreditLimit($companyId));

            try {
                $data = [
                    'entityId' => $entityId,
                    'companyId' => $companyId,
                    'oldCompanyId' => $oldCompanyId,
                ];
                foreach ($this->getCompanyUsers($entityId) as $user) {
                    $this->resetUserCartSession($user);
                }
                $this->companyRepository->save($hokodoCompany);
                $result = [
                    'success' => true,
                    'message' => __('Success, the company was successfully saved.'),
                ];

                $data = array_merge($data, $result);
                $this->logger->debug(__METHOD__, $data);
            } catch (\Exception $e) {
                $result = [
                    'success' => false,
                    'message' => __('Error, the company has not been updated. %1', $e->getMessage()),
                ];

                $data = array_merge($data, $result);
                $this->logger->error(__METHOD__, $data);
            }
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }

    /**
     * Get company's users.
     *
     * @param int $entityId
     *
     * @return array
     *
     * @throws NoSuchEntityException
     */
    private function getCompanyUsers(int $entityId): array
    {
        $company = $this->magentoCompanyRepository->get($entityId);
        $users = $this->companyUsers->getCompanyUsers($company, ['pageSize' => 0, 'currentPage' => 0]);

        return $users->getItems();
    }

    /**
     * Reset user's session if the companyId changed.
     *
     * @param CustomerInterface $user
     *
     * @return void
     */
    private function resetUserCartSession(CustomerInterface $user): void
    {
        try {
            $cart = $this->cartRepository->getActiveForCustomer($user->getId());
            if ($cart->getId()) {
                $hokodoQuote = $this->hokodoQuoteRepository->getByQuoteId($cart->getId());
                if ($hokodoQuote->getQuoteId()) {
                    $this->hokodoQuoteRepository->deleteByQuoteId($cart->getId());
                }
                $this->sessionCleaner->clearFor((int) $user->getId());
            }
        } catch (NoSuchEntityException $e) {
            $data = [
                'userId' => $user->getId(),
                'message' => __('Can not reset session if the companyId changed for user %1', $user->getId()),
                'error' => $e->getMessage(),
            ];
            $this->logger->error(__METHOD__, $data);
        }
    }

    /**
     * Get company credit limit.
     *
     * @param string $companyId
     *
     * @return CreditLimitInterface|null
     *
     * @throws NoSuchEntityException
     */
    private function getCompanyCreditLimit(string $companyId): ?CreditLimitInterface
    {
        /** @var CompanyCreditRequestInterface $searchRequest */
        $searchRequest = $this->companyCreditRequestFactory->create();
        $searchRequest
            ->setCurrency($this->storeManager->getStore()->getCurrentCurrencyCode())
            ->setCompanyId($companyId);

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
