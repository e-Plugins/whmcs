<?php
namespace Digiwallet\Packages\Transaction\Client\InvoiceLine;

/**
 * Class InvoiceLine
 * @package Digiwallet\Packages\Transaction\Client\InvoiceLine
 */
class InvoiceLine implements InvoiceLineInterface
{
    public const VAT_HIGH = 1;
    public const VAT_LOW = 2;
    public const VAT_NULL = 3;
    public const VAT_NONE = 4;

    public const VAT_TYPES = [
        self::VAT_HIGH,
        self::VAT_LOW,
        self::VAT_NULL,
        self::VAT_NONE
    ];

    /**
     * @var string
     */
    private $productCode;

    /**
     * @var string
     */
    private $productDescription;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @var int
     */
    private $price;

    /**
     * @var int
     */
    private $taxCategory;

    /**
     * InvoiceLine constructor.
     * @param string $productCode
     * @param string $productDescription
     * @param int $quantity
     * @param int $price
     * @param string $taxCategory
     */
    public function __construct(
        string $productCode,
        string $productDescription,
        int $quantity,
        int $price,
        string $taxCategory
    ) {
        $this->productCode = $productCode;
        $this->productDescription = $productDescription;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->taxCategory = $taxCategory;
    }

    /**
     * @return string
     */
    public function productCode(): string
    {
        return $this->productCode;
    }

    /**
     * @return string
     */
    public function productDescription(): string
    {
        return $this->productDescription;
    }

    /**
     * @return int
     */
    public function quantity(): int
    {
        return $this->quantity;
    }

    /**
     * @return int
     */
    public function price(): int
    {
        return $this->price;
    }

    /**
     * @return int
     */
    public function taxCategory(): int
    {
        return $this->taxCategory;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return  [
            'productCode' => $this->productCode,
            'productDescription' => $this->productDescription,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'taxCategory' => $this->taxCategory,
        ];
    }
}
