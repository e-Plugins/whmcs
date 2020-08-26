<?php
namespace Digiwallet\Packages\Transaction\Client\InvoiceLine;

use Digiwallet\Packages\Transaction\Client\InvoiceLine\InvoiceLineInterface as InvoiceLine;
/**
 * Interface FactoryInterface
 * @package Digiwallet\Packages\Transaction\Client\InvoiceLine
 */
interface FactoryInterface
{
    /**
     * @param string $productCode
     * @param string $productDescription
     * @param int $quantity
     * @param int $price
     * @param string $taxCategory
     * @return InvoiceLineInterface
     */
    public function create(
        string $productCode,
        string $productDescription,
        int $quantity,
        int $price,
        string $taxCategory
    ): InvoiceLine;
}
