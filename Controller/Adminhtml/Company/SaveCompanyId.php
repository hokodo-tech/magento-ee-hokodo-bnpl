<?php
/**
 * Copyright © 2018-2023 Hokodo. All Rights Reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Hokodo\BnplCommerce\Controller\Adminhtml\Company;

use Hokodo\BNPL\Api\CompanyCreditServiceInterface;
use Hokodo\BNPL\Api\Data\Gateway\CreateOrganisationRequestInterfaceFactory;
use Hokodo\BNPL\Api\HokodoQuoteRepositoryInterface;
use Hokodo\BNPL\Gateway\Service\Organisation;
use Hokodo\BnplCommerce\Api\CompanyRepositoryInterface;
use Hokodo\BnplCommerce\Model\RequestBuilder\OrganisationBuilder;
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
use Magento\Framework\Exception\NotFoundException;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Quote\Api\CartRepositoryInterface;
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
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var CompanyCreditServiceInterface
     */
    private CompanyCreditServiceInterface $companyCreditService;

    /**
     * @var OrganisationBuilder
     */
    private OrganisationBuilder $organisationBuilder;

    /**
     * @var CreateOrganisationRequestInterfaceFactory
     */
    private CreateOrganisationRequestInterfaceFactory $createOrganisationRequestFactory;

    /**
     * @var Organisation
     */
    private Organisation $organisationService;

    /**
     * SaveCompanyId constructor.
     *
     * @param Context                                   $context
     * @param CompanyRepositoryInterface                $companyRepository
     * @param CartRepositoryInterface                   $cartRepository
     * @param SessionCleanerInterface                   $sessionCleaner
     * @param CompanyUsers                              $companyUsers
     * @param MagentoCompanyRepositoryInterface         $magentoCompanyRepository
     * @param HokodoQuoteRepositoryInterface            $hokodoQuoteRepository
     * @param LoggerInterface                           $logger
     * @param CompanyCreditServiceInterface             $companyCreditService
     * @param OrganisationBuilder                       $organisationBuilder
     * @param CreateOrganisationRequestInterfaceFactory $createOrganisationRequestFactory
     * @param Organisation                              $organisationService
     */
    public function __construct(
        Context $context,
        CompanyRepositoryInterface $companyRepository,
        CartRepositoryInterface $cartRepository,
        SessionCleanerInterface $sessionCleaner,
        CompanyUsers $companyUsers,
        MagentoCompanyRepositoryInterface $magentoCompanyRepository,
        HokodoQuoteRepositoryInterface $hokodoQuoteRepository,
        LoggerInterface $logger,
        CompanyCreditServiceInterface $companyCreditService,
        OrganisationBuilder $organisationBuilder,
        CreateOrganisationRequestInterfaceFactory $createOrganisationRequestFactory,
        Organisation $organisationService
    ) {
        parent::__construct($context);
        $this->companyRepository = $companyRepository;
        $this->cartRepository = $cartRepository;
        $this->sessionCleaner = $sessionCleaner;
        $this->companyUsers = $companyUsers;
        $this->magentoCompanyRepository = $magentoCompanyRepository;
        $this->hokodoQuoteRepository = $hokodoQuoteRepository;
        $this->logger = $logger;
        $this->companyCreditService = $companyCreditService;
        $this->organisationBuilder = $organisationBuilder;
        $this->createOrganisationRequestFactory = $createOrganisationRequestFactory;
        $this->organisationService = $organisationService;
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

            try {
                $hokodoCompany->setCreditLimit($this->companyCreditService->getCreditLimit($companyId));
                $hokodoCompany->setOrganisationId($this->getOrganisationId($companyId, (string) $entityId));

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
     * Get hokodo organisation id.
     *
     * @param string $hokodoCompanyId
     * @param string $magentoCompanyId
     *
     * @return string
     *
     * @throws CommandException
     * @throws NotFoundException
     */
    private function getOrganisationId(string $hokodoCompanyId, string $magentoCompanyId): string
    {
        $createOrganisationRequest = $this->createOrganisationRequestFactory->create();
        $createOrganisationRequest
            ->setCompanyId($hokodoCompanyId)
            ->setUniqueId($this->organisationBuilder->generateUniqueId($hokodoCompanyId . $magentoCompanyId))
            ->setRegistered(date('Y-m-d\TH:i:s\Z'));

        $organisation = $this->organisationService->createOrganisation($createOrganisationRequest);
        if ($organisation) {
            return $organisation->getDataModel()->getId();
        }

        return '';
    }
}
