<?php

namespace Hokodo\BnplCommerce\Controller\Adminhtml\Company;

use Hokodo\BNPL\Api\HokodoQuoteRepositoryInterface;
use Hokodo\BnplCommerce\Api\CompanyRepositoryInterface;
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

class SaveCompanyId extends Action implements HttpPostActionInterface
{
    /**
     * @var \Hokodo\BnplCommerce\Api\CompanyRepositoryInterface
     */
    private CompanyRepositoryInterface $companyRepository;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepository;

    /**
     * @var \Magento\Customer\Api\SessionCleanerInterface
     */
    private SessionCleanerInterface $sessionCleaner;

    /**
     * @var \Magento\CompanyGraphQl\Model\Company\Users
     */
    private CompanyUsers $companyUsers;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private MagentoCompanyRepositoryInterface $magentoCompanyRepository;

    /**
     * @var \Hokodo\BNPL\Api\HokodoQuoteRepositoryInterface
     */
    private HokodoQuoteRepositoryInterface $hokodoQuoteRepository;

    /**
     * SaveCompanyId constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Hokodo\BnplCommerce\Api\CompanyRepositoryInterface $companyRepository
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Customer\Api\SessionCleanerInterface $sessionCleaner
     * @param \Magento\CompanyGraphQl\Model\Company\Users $companyUsers
     * @param \Magento\Company\Api\CompanyRepositoryInterface $magentoCompanyRepository
     * @param \Hokodo\BNPL\Api\HokodoQuoteRepositoryInterface $hokodoQuoteRepository
     */
    public function __construct(
        Context                           $context,
        CompanyRepositoryInterface        $companyRepository,
        CartRepositoryInterface           $cartRepository,
        SessionCleanerInterface           $sessionCleaner,
        CompanyUsers                      $companyUsers,
        MagentoCompanyRepositoryInterface $magentoCompanyRepository,
        HokodoQuoteRepositoryInterface    $hokodoQuoteRepository
    ) {
        parent::__construct($context);
        $this->companyRepository = $companyRepository;
        $this->cartRepository = $cartRepository;
        $this->sessionCleaner = $sessionCleaner;
        $this->companyUsers = $companyUsers;
        $this->magentoCompanyRepository = $magentoCompanyRepository;
        $this->hokodoQuoteRepository = $hokodoQuoteRepository;
    }

    /**
     * Execute action based on request and return result.
     *
     * @return \Magento\Framework\Controller\ResultInterface
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
            if (! $hokodoCompany->getId()) {
                $hokodoCompany->setId($entityId);
            }
            $hokodoCompany->setCompanyId($companyId);

            try {
                foreach ($this->getCompanyUsers($entityId) as $user) {
                    $this->resetUserCartSession($user);
                }
                $this->companyRepository->save($hokodoCompany);
                $result = [
                    'success' => true,
                    'message' => __('Success, the company was successfully saved.'),
                ];
            } catch (\Exception $e) {
                $result = [
                    'success' => false,
                    'message' => __('Error, the company has not been updated. %1', $e->getMessage()),
                ];
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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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
     * @param \Magento\Customer\Api\Data\CustomerInterface $user
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
                $this->sessionCleaner->clearFor($user->getId());
            }
        } catch (NoSuchEntityException $e) { // @codingStandardsIgnoreLine
        } // @codingStandardsIgnoreLine
    }
}
