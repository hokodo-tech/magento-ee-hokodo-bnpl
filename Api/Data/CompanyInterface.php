<?php
/**
 * Copyright © 2018-2023 Hokodo. All Rights Reserved.
 * See LICENSE for license details.
 */

namespace Hokodo\BnplCommerce\Api\Data;

use Hokodo\BNPL\Api\Data\Company\CreditLimitInterface;
use Hokodo\BNPL\Api\Data\HokodoEntityInterface;

interface CompanyInterface extends HokodoEntityInterface
{
    /**
     * String constants for property names.
     */
    public const ID = 'id';
    public const ENTITY_ID = 'entity_id';
    public const COMPANY_ID = 'company_id';
    public const ORGANISATION_ID = 'organisation_id';
    public const CREDIT_LIMIT = 'credit_limit';

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
     * Organisation Id getter.
     *
     * @return string|null
     */
    public function getOrganisationId(): ?string;

    /**
     * Organisation Id setter.
     *
     * @param string $organisationId
     *
     * @return $this
     */
    public function setOrganisationId(string $organisationId): self;

    /**
     * Credit Limit getter.
     *
     * @return \Hokodo\BNPL\Api\Data\Company\CreditLimitInterface|null
     */
    public function getCreditLimit(): ?CreditLimitInterface;

    /**
     * Credit Limit setter.
     *
     * @param \Hokodo\BNPL\Api\Data\Company\CreditLimitInterface|null $creditLimit
     *
     * @return $this
     */
    public function setCreditLimit(?CreditLimitInterface $creditLimit): self;
}
