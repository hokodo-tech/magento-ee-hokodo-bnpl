<?php

namespace Hokodo\BnplCommerce\Api\Data;

interface CompanyInterface
{
    /**
     * String constants for property names
     */
    public const ID         = "id";
    public const ENTITY_ID  = "entity_id";
    public const COMPANY_ID = "company_id";

    /**
     * Getter for Id.
     *
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * Setter for Id.
     *
     * @param int|null $id
     *
     * @return self
     */
    public function setId(?int $id): self;

    /**
     * Getter for EntityId.
     *
     * @return int|null
     */
    public function getEntityId(): ?int;

    /**
     * Setter for EntityId.
     *
     * @param int|null $entityId
     *
     * @return self
     */
    public function setEntityId(?int $entityId): self;

    /**
     * Getter for CompanyId.
     *
     * @return string|null
     */
    public function getCompanyId(): ?string;

    /**
     * Setter for CompanyId.
     *
     * @param string|null $companyId
     *
     * @return self
     */
    public function setCompanyId(?string $companyId): self;
}
