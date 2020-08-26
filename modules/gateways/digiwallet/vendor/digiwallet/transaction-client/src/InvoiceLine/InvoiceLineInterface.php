<?php
namespace Digiwallet\Packages\Transaction\Client\InvoiceLine;

use JsonSerializable;

/**
 * Interface InvoiceLineInterface
 * @package Digiwallet\Packages\Transaction\Client\InvoiceLine
 */
interface InvoiceLineInterface extends JsonSerializable
{
    /**
     * @return string
     */
    public function productCode(): string;

    /**
     * @return string
     */
    public function productDescription(): string;

    /**
     * @return int
     */
    public function quantity(): int;

    /**
     * @return int
     */
    public function price(): int;

    /**
     * @return int
     */
    public function taxCategory(): int;
}
