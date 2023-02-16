<?php

declare(strict_types=1);

namespace Hokodo\BnplCommerce\Plugin\Customer;

use Hokodo\BNPL\Api\HokodoCustomerRepositoryInterface;
use Hokodo\BNPL\Api\HokodoEntityTypeResolverInterface;
use Hokodo\BnplCommerce\Model\Config\Source\EntityLevelForSave;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Psr\Log\LoggerInterface;

class RemoveUserFromOrganisation
{
    /**
     * @var string|null
     */
    private $customerCompanyBeforeSave;

    /**
     * @var HokodoCustomerRepositoryInterface
     */
    private HokodoCustomerRepositoryInterface $hokodoCustomerRepository;

    /**
     * @var HokodoEntityTypeResolverInterface
     */
    private HokodoEntityTypeResolverInterface $entityTypeResolver;

    /**
     * @var CommandPoolInterface
     */
    private CommandPoolInterface $commandPool;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * RemoveUserFromOrganisation constructor.
     *
     * @param HokodoCustomerRepositoryInterface $hokodoCustomerRepository
     * @param HokodoEntityTypeResolverInterface $entityTypeResolver
     * @param LoggerInterface                   $logger
     * @param CommandPoolInterface              $commandPool
     */
    public function __construct(
        HokodoCustomerRepositoryInterface $hokodoCustomerRepository,
        HokodoEntityTypeResolverInterface $entityTypeResolver,
        LoggerInterface $logger,
        //TODO refactor when main module clean is finished.
        CommandPoolInterface $commandPool
    ) {
        $this->hokodoCustomerRepository = $hokodoCustomerRepository;
        $this->entityTypeResolver = $entityTypeResolver;
        $this->logger = $logger;
        $this->commandPool = $commandPool;
    }

    /**
     * After save method.
     *
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface           $result
     * @param CustomerInterface           $customer
     * @param null                        $passwordHash
     *
     * @return CustomerInterface
     */
    public function afterSave(
        CustomerRepositoryInterface $subject,
        CustomerInterface $result,
        CustomerInterface $customer,
        $passwordHash = null
    ): CustomerInterface {
        if ($this->isCustomerCanBeProcessed() && $this->isCompanyChanged($customer, $result)) {
            $this->removeUserFromOrganisation((int) $result->getId());
        }
        return $result;
    }

    /**
     * After delete method.
     *
     * @param CustomerRepositoryInterface $subject
     * @param bool                        $result
     * @param string|int                  $customerId
     *
     * @return bool
     */
    public function afterDeleteById(
        CustomerRepositoryInterface $subject,
        $result,
        $customerId
    ): bool {
        if ($result && $this->isCustomerCanBeProcessed()) {
            $this->removeUserFromOrganisation((int) $customerId);
        }
        return $result;
    }

    /**
     * Remove user from Hokodo organisation.
     *
     * @param int $customerId
     *
     * @return void
     */
    public function removeUserFromOrganisation(int $customerId): void
    {
        $hokodoCustomer = $this->hokodoCustomerRepository->getByCustomerId($customerId);
        if ($hokodoCustomer->getUserId() && $hokodoCustomer->getOrganisationId()) {
            try {
                $this->commandPool->get('organisation_user_remove')->execute(
                    [
                        'organisation_id' => $hokodoCustomer->getOrganisationId(),
                        'user_id' => $hokodoCustomer->getUserId(),
                    ]
                );
                $hokodoCustomer->setOrganisationId('');
                $this->hokodoCustomerRepository->save($hokodoCustomer);
            } catch (\Exception $e) {
                $data = [
                    'message' => 'Hokodo_BNPL: remove user from organisation failed with error',
                    'error' => $e->getMessage(),
                ];
                $this->logger->debug(__METHOD__, $data);
            }
        }
    }

    /**
     * Checks whether logic can be executed.
     *
     * @return bool
     */
    private function isCustomerCanBeProcessed(): bool
    {
        return $this->entityTypeResolver->resolve() === EntityLevelForSave::COMPANY;
    }

    /**
     * Checks company Ids before and after save.
     *
     * @param CustomerInterface $customer
     * @param CustomerInterface $updatedCustomer
     *
     * @return bool
     */
    private function isCompanyChanged(CustomerInterface $customer, CustomerInterface $updatedCustomer): bool
    {
        return $customer->getExtensionAttributes()?->getCompanyAttributes()?->getCompanyId() !==
            $updatedCustomer->getExtensionAttributes()?->getCompanyAttributes()?->getCompanyId();
    }
}
