<?php

declare(strict_types=1);

namespace Hokodo\BnplCommerce\Model\Data;

use Hokodo\BnplCommerce\Api\Data\CompanyInterface;
use Magento\Framework\DataObject;

class Company extends DataObject implements CompanyInterface
{
    /**
     * Getter for Id.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->getData(self::ID) === null ? null
            : (int) $this->getData(self::ID);
    }

    /**
     * Setter for Id.
     *
     * @param int|null $id
     *
     * @return self
     */
    public function setId(?int $id): self
    {
        $this->setData(self::ID, $id);
        return $this;
    }

    /**
     * Getter for EntityId.
     *
     * @return int|null
     */
    public function getEntityId(): ?int
    {
        return $this->getData(self::ENTITY_ID) === null ? null
            : (int) $this->getData(self::ENTITY_ID);
    }

    /**
     * Setter for EntityId.
     *
     * @param int|null $entityId
     *
     * @return self
     */
    public function setEntityId(?int $entityId): self
    {
        $this->setData(self::ENTITY_ID, $entityId);
        return $this;
    }

    /**
     * Getter for CompanyId.
     *
     * @return string|null
     */
    public function getCompanyId(): ?string
    {
        return $this->getData(self::COMPANY_ID);
    }

    /**
     * Setter for CompanyId.
     *
     * @param string|null $companyId
     *
     * @return self
     */
    public function setCompanyId(?string $companyId): self
    {
        $this->setData(self::COMPANY_ID, $companyId);
        return $this;
    }

    /**
     * Getter for OrganizationId.
     *
     * @return string|null
     */
    public function getOrganisationId(): ?string
    {
        return $this->getData(self::ORGANISATION_ID);
    }

    /**
     * Setter for OrganizationId.
     *
     * @param string|null $organisationId
     *
     * @return self
     */
    public function setOrganisationId(?string $organisationId): self
    {
        $this->setData(self::ORGANISATION_ID, $organisationId);
        return $this;
    }
}
