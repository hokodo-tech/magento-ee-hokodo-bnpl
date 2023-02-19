<?php

declare(strict_types=1);

namespace Hokodo\BnplCommerce\Plugin\Customer;

use Hokodo\BNPL\Api\Data\HokodoCustomerInterface;
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
     * @var HokodoCustomerInterface|null
     */
    private $hokodoCustomerToDelete;

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
     * Save hokodo user before it will be deleted from DB.
     *
     * @param CustomerRepositoryInterface $subject
     * @param string|int                  $customerId
     *
     * @return void
     */
    public function beforeDeleteById(CustomerRepositoryInterface $subject, $customerId)
    {
        $this->hokodoCustomerToDelete = $this->hokodoCustomerRepository->getByCustomerId((int) $customerId);
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
            $this->removeUserFromOrganisation();
        }
        return $result;
    }

    /**
     * Remove user from Hokodo organisation.
     *
     * @return void
     */
    public function removeUserFromOrganisation(): void
    {
        if ($this->hokodoCustomerToDelete
            && $this->hokodoCustomerToDelete->getUserId()
            && $this->hokodoCustomerToDelete->getOrganisationId()
        ) {
            try {
                $this->commandPool->get('organisation_user_remove')->execute(
                    [
                        'organisation_id' => $this->hokodoCustomerToDelete->getOrganisationId(),
                        'user_id' => $this->hokodoCustomerToDelete->getUserId(),
                    ]
                );
                $this->hokodoCustomerToDelete = null;
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
