<?php
namespace Digiwallet\Packages\Transaction\Client\InvoiceLine;

/**
 * Class Factory
 * @package Digiwallet\Packages\Transaction\Client\InvoiceLine
 */
class Factory implements FactoryInterface
{
    public function create(
        string $productCode,
        string $productDescription,
        int $quantity,
        int $price,
        string $taxCategory
    ): InvoiceLineInterface {
        return new InvoiceLine($productCode, $productDescription, $quantity, $price, $taxCategory);
    }
}
