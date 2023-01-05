<?php

namespace Hokodo\BnplCommerce\Api\Data\Company;

interface CreditLimitInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    public const CURRENCY = 'currency';
    public const AMOUNT_AVAILABLE = 'amount_available';
    public const AMOUNT_IN_USE = 'amount_in_use';
    public const AMOUNT = 'amount';

    /**
     * Currency getter.
     *
     * @return string
     */
    public function getCurrency(): string;

    /**
     * Currency setter.
     *
     * @param string $currency
     *
     * @return $this
     */
    public function setCurrency(string $currency): self;

    /**
     * Amount Available getter.
     *
     * @return int
     */
    public function getAmountAvailable(): int;

    /**
     * Amount Available setter.
     *
     * @param int $amountAvailable
     *
     * @return $this
     */
    public function setAmountAvailable(int $amountAvailable): self;

    /**
     * Amount In Use getter.
     *
     * @return int
     */
    public function getAmountInUse(): int;

    /**
     * Amount In Use setter.
     *
     * @param int $amountInUse
     *
     * @return $this
     */
    public function setAmountInUse(int $amountInUse): self;

    /**
     * Amount getter.
     *
     * @return int
     */
    public function getAmount(): int;

    /**
     * Amount setter.
     *
     * @param int $amount
     *
     * @return $this
     */
    public function setAmount(int $amount): self;
}