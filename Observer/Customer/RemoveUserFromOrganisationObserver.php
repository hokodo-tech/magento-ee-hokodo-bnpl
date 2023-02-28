<?php

namespace Hokodo\BnplCommerce\Observer\Customer;

use Hokodo\BNPL\Api\HokodoCustomerRepositoryInterface;
use Hokodo\BNPL\Api\HokodoEntityTypeResolverInterface;
use Hokodo\BnplCommerce\Model\Config\Source\EntityLevelForSave;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Psr\Log\LoggerInterface;

/**
 * Observes the `customer_save_after_data_object` event.
 */
class RemoveUserFromOrganisationObserver implements ObserverInterface
{
    /**
     * @var HokodoCustomerRepositoryInterface
     */
    private HokodoCustomerRepositoryInterface $hokodoCustomerRepository;

    /**
     * @var HokodoEntityTypeResolverInterface
     */
    private HokodoEntityTypeResolverInterface $entityTypeResolver;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var CommandPoolInterface
     */
    private CommandPoolInterface $commandPool;

    /**
     * RemoveUserFromOrganisationObserver constructor.
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
     * Observer for customer_save_after_data_object.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        $originalCustomer = $observer->getEvent()->getOrigCustomerDataObject();
        if (!$originalCustomer) {
            return;
        }

        $customer = $observer->getEvent()->getCustomerDataObject();
        if ($this->isCustomerCanBeProcessed() && $this->isCompanyChanged($originalCustomer, $customer)) {
            $this->removeUserFromOrganisation($customer);
        }
    }

    /**
     * Remove user from Hokodo organisation.
     *
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function removeUserFromOrganisation(CustomerInterface $customer): void
    {
        $hokodoCustomer = $this->hokodoCustomerRepository->getByCustomerId($customer->getId());
        if ($hokodoCustomer->getCustomerId()
            && $hokodoCustomer->getUserId()
            && $hokodoCustomer->getOrganisationId()
        ) {
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
