<?php
namespace Digiwallet\Packages\Transaction\Client\Request;

use BadMethodCallException;
use Digiwallet\Packages\Transaction\Client\InvoiceLine\InvoiceLineInterface as InvoiceLine;
use Digiwallet\Packages\Transaction\Client\Response\CreateTransactionInterface as CreateTransactionResponse;
use Digiwallet\Packages\Transaction\Client\Response\CreateTransactionInterface as CreateTransactionResponseInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\RequestInterface;

/**
 * Interface TransactionRequest
 * @package Digiwallet\Packages\Transaction\Client\Request
 */
interface CreateTransactionInterface extends RequestInterface
{
    /**
     * in case of a transaction with variable amount also specify $maxAmount. This will exclude Afterpay.
     * in case of a transaction with fixed amount only specify $minAmount
     * Both $minAmount and $maxAmount should be in cents so for 1 euro you should enter 100
     * @param int $minAmount
     * @param int|null $maxAmount
     * @return CreateTransactionInterface
     */
    public function withAmount(int $minAmount, int $maxAmount = null): self;

    /**
     * @param string $consumerEmail
     * @return CreateTransactionInterface
     */
    public function withConsumerEmail(string $consumerEmail): self;

    /**
     * @param string $consumerIp
     * @return CreateTransactionInterface
     */
    public function withConsumerIp(string $consumerIp): self;

    /**
     * @param string $currencyCode
     * @return CreateTransactionInterface
     */
    public function withCurrency(string $currencyCode): self;

    /**
     * @param string $description
     * @return CreateTransactionInterface
     */
    public function withDescription(string $description): self;

    /**
     * @param int $outletId
     * @return CreateTransactionInterface
     */
    public function withOutlet(int $outletId): self;

    /**
     * @param string $bearer
     * @return CreateTransactionInterface
     */
    public function withBearer(string $bearer): self;

    /**
     * @param string $header
     * @param string $value
     * @return CreateTransactionInterface
     */
    public function withHeaderValue(string $header, string $value): CreateTransactionInterface;

    /**
     * @param int $environment
     * @return CreateTransactionInterface
     */
    public function withEnvironment(int $environment): self;

    /**
     * @param string $preferredLanguage
     * @return CreateTransactionInterface
     */
    public function withLanguagePreference(string $preferredLanguage): self;

    /**
     * @param string $reportUrl
     * @return CreateTransactionInterface
     */
    public function withReportUrl(string $reportUrl): self;

    /**
     * @param string $cancelUrl
     * @return CreateTransactionInterface
     */
    public function withCancelUrl(string $cancelUrl): self;

    /**
     * @param string $tariffGroup
     * @return CreateTransactionInterface
     */
    public function withTariffGroup(string $tariffGroup): CreateTransactionInterface;

    /**
     * @param string $returnUrl
     * @return CreateTransactionInterface
     */
    public function withReturnUrl(string $returnUrl): self;

    /**
     * @param string[]|iterable $paymentMethods
     * @return CreateTransactionInterface
     */
    public function withPaymentMethods(iterable $paymentMethods): self;

    /**
     * @return bool
     */
    public function validate(): bool;

    /**
     * @param int $productTypeId
     * @return CreateTransactionInterface
     */
    public function withProductType(int $productTypeId): self;

    /**
     * @param InvoiceLine[]|array $invoiceLines
     * @return CreateTransactionInterface
     */
    public function withInvoiceLines(array $invoiceLines): self;

    /**
     * @return CreateTransactionResponseInterface
     * @throws GuzzleException
     * @throws BadMethodCallException
     */
    public function send(): CreateTransactionResponse;
}
