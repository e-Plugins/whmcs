<?php

namespace Digiwallet\Packages\Transaction\Client\Request;

use BadMethodCallException;
use Digiwallet\Packages\Transaction\Client\ClientInterface as TransactionClient;
use Digiwallet\Packages\Transaction\Client\InvoiceLine\InvoiceLineInterface as InvoiceLine;
use Digiwallet\Packages\Transaction\Client\Payment\Payment;
use Digiwallet\Packages\Transaction\Client\Response\CreateTransaction as CreateTransactionResponse;
use Digiwallet\Packages\Transaction\Client\Response\CreateTransactionInterface as CreateTransactionResponseInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use function GuzzleHttp\Psr7\stream_for;

/**
 * Class CreateTransaction
 * @package Digiwallet\Packages\Transaction\Client\Request
 */
class CreateTransaction extends Request implements CreateTransactionInterface
{
    private const DIGIWALLET_PAY_CREATE_TRANSACTION_PATH = '/unified/transaction';
    private const DIGIWALLET_PAY_CREATE_TRANSACTION_HTTP_METHOD = 'POST';

    private const DIGIWALLET_PAY_PROD_ENVIRONMENT = 0;
    private const DIGIWALLET_PAY_TEST_PANEL_ENVIRONMENT = 1;
    private const DIGIWALLET_PAY_ACQUIRER_PREPROD_ENVIRONMENT = 2;

    /**
     * @var array
     */
    private $options = [
        'outletId' => null,
        'currencyCode' => 'EUR',
        'consumerEmail' => null,
        'description' => null,
        'returnUrl' => null,
        'reportUrl' => null,
        'cancelUrl' => null,
        'consumerIp' => null,
        'preferredLanguage' => null,
        'sofortProductTypeId' => null,
        'amountChangeable' => false,
        'inputAmount' => null,
        'inputAmountMin' => null,
        'inputAmountMax' => null,
        'paymentMethods' => [],
        'environment' => 0,
        'test' => 0,
        'acquirerPreprodTest' => 0,
        'tariffGroup' => null
    ];
    private $headers = [];

    /**
     * @var array
     */
    private $invoiceLines = [];

    /**
     * @var TransactionClient
     */
    private $client;

    /**
     * CreateTransaction constructor.
     * @param TransactionClient $client
     * @param array $options
     */
    public function __construct(TransactionClient $client, array $options = [])
    {
        parent::__construct(
            self::DIGIWALLET_PAY_CREATE_TRANSACTION_HTTP_METHOD,
            self::DIGIWALLET_PAY_CREATE_TRANSACTION_PATH
        );

        $this->client = $client;
        $this->withOptions($options);
    }

    /**
     * @param string $description
     * @return CreateTransactionInterface
     */
    public function withDescription(string $description): CreateTransactionInterface
    {
        return $this->withOption('description', $description);
    }

    /**
     * @param string $preferredLanguage
     * @return CreateTransactionInterface
     */
    public function withLanguagePreference(string $preferredLanguage): CreateTransactionInterface
    {
        return $this->withOption('preferredLanguage', $preferredLanguage);
    }

    /**
     * @param string $consumerEmail
     * @return CreateTransactionInterface
     */
    public function withConsumerEmail(string $consumerEmail): CreateTransactionInterface
    {

        return $this->withOption('consumerEmail', $consumerEmail);
    }

    /**
     * @param string $consumerIp
     * @return CreateTransactionInterface
     */
    public function withConsumerIp(string $consumerIp): CreateTransactionInterface
    {
        return $this->withOption('consumerIp', $consumerIp);
    }

    /**
     * @param string $reportUrl
     * @return CreateTransactionInterface
     */
    public function withReportUrl(string $reportUrl): CreateTransactionInterface
    {
        return $this->withOption('reportUrl', $reportUrl);
    }

    /**
     * @param string $returnUrl
     * @return CreateTransactionInterface
     */
    public function withReturnUrl(string $returnUrl): CreateTransactionInterface
    {
        return $this->withOption('returnUrl', $returnUrl);
    }

    /**
     * @param string $paymentMethod
     * @return CreateTransactionInterface
     */
    public function withTariffGroup(string $tariffGroup): CreateTransactionInterface
    {
        return $this->withOption('tariffGroup', $tariffGroup);
    }

    /**
     * @param string $cancelURL
     * @return CreateTransactionInterface
     */
    public function withCancelUrl(string $cancelURL): CreateTransactionInterface
    {
        return $this->withOption('cancelUrl', $cancelURL);
    }

    /**
     * @param string $currencyCode
     * @return CreateTransactionInterface
     */
    public function withCurrency(string $currencyCode): CreateTransactionInterface
    {
        return $this->withOption('currencyCode', $currencyCode);
    }

    /**
     * in case of transaction with variable amount, specify both $amount and $maxAmount. This excludes Afterpay.
     * Both amounts should be in cents so for 1 euro you should enter 100
     * @param int $amount
     * @param int|null $maxAmount
     * @return CreateTransactionInterface
     */
    public function withAmount(int $amount, int $maxAmount = null): CreateTransactionInterface
    {
        return $this
            ->withOption('inputAmount', $amount)
            ->withOption('inputAmountMin', $amount)
            ->withOption('inputAmountMax', $maxAmount)
            ->withOption('amountChangeable', $maxAmount);
    }

    /**
     * @param int $productTypeId
     * @return CreateTransaction
     */
    public function withProductType(int $productTypeId): CreateTransactionInterface
    {
        return $this
            ->withOption('enabledSofort', true)
            ->withOption('sofortProductTypeId', $productTypeId);
    }

    /**
     * @param InvoiceLine[]|array $invoiceLines
     * @return CreateTransactionInterface
     */
    public function withInvoiceLines(array $invoiceLines): CreateTransactionInterface
    {
        foreach ($invoiceLines as $invoiceLine) {
            $this->withInvoiceLine($invoiceLine);
        }

        return $this;
    }

    /**
     * @param string[]|iterable $paymentMethods
     * @return CreateTransactionInterface
     */
    public function withPaymentMethods(iterable $paymentMethods): CreateTransactionInterface
    {
        $this->options['paymentMethods'] = [];
        foreach ($paymentMethods as $paymentMethod) {
            $this->withPaymentMethod($paymentMethod);
        }

        return $this;
    }

    /**
     * @param int $outletId
     * @return CreateTransactionInterface
     */
    public function withOutlet(int $outletId): CreateTransactionInterface
    {
        return $this->withOption('outletId', $outletId);
    }

    /**
     * @param string $bearer
     * @return CreateTransactionInterface
     */
    public function withBearer(string $bearer): CreateTransactionInterface
    {
        $this->headers['Authorization'] = 'Bearer ' . $bearer;
        return $this;
    }

    /**
     * Add an extra header to the request
     *
     * @param string $header
     * @param string $value
     * @return CreateTransactionInterface
     */
    public function withHeaderValue(string $header, string $value): CreateTransactionInterface
    {
        $this->headers[$header] = $value;
        return $this;
    }

    /**
     * @param int $environment
     * @return CreateTransactionInterface
     */
    public function withEnvironment(int $environment): CreateTransactionInterface
    {
        if ($environment === self::DIGIWALLET_PAY_TEST_PANEL_ENVIRONMENT) {
            return $this->withOption('test', $environment);
        }

        if ($environment === self::DIGIWALLET_PAY_ACQUIRER_PREPROD_ENVIRONMENT) {
            return $this->withOption('acquirerPreprodTest', 1);
        }

        return $this->withOption('test', self::DIGIWALLET_PAY_PROD_ENVIRONMENT);
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        switch (true) {
            case empty($this->options['description']):
            case $this->options['outletId'] < 1:
            case empty($this->options['returnUrl']) || !filter_var($this->options['returnUrl'], FILTER_VALIDATE_URL):
            case empty($this->options['inputAmount']) && (empty($this->options['inputAmountMin']) && empty($this->options['inputAmountMax'])):
                return false;
        }

        if (empty($this->options['sofortProductTypeId']) && in_array(Payment::SOFORT, $this->options['paymentMethods'],
                true)) {
            return false;
        }

        if ((empty($this->invoiceLines) || $this->options['amountChangeable']) && in_array(Payment::AFTERPAY,
                $this->options['paymentMethods'], true)) {
            return false;
        }

        return true;
    }

    /**
     * @return CreateTransactionResponseInterface
     * @throws GuzzleException
     * @throws BadMethodCallException
     */
    public function send(): CreateTransactionResponseInterface
    {
        if (!$this->validate()) {
            throw new BadMethodCallException('Missing required options');
        }

        $request = $this->buildRequest();
        $request = $request->withAddedHeader('Content-Type', 'application/json');

        foreach ($this->headers as $header => $value) {
            $request = $request->withAddedHeader($header, $value);
        }

        $response = $this->client->createTransaction($request);

        return new CreateTransactionResponse($response);
    }

    /**
     * @return CreateTransactionInterface
     */
    private function buildRequest(): CreateTransactionInterface
    {
        $body = [
            'outletID' => $this->options['outletId'],
            'currencyCode' => $this->options['currencyCode'],
            'description' => $this->options['description'],
            'returnURL' => $this->options['returnUrl'],
            'paymentOptions' => [
                'amountChangeable' => $this->options['amountChangeable']
            ]
        ];

        if ($this->options['consumerEmail'] !== null) {
            $body['consumerEmail'] = $this->options['consumerEmail'];
        }

        if ($this->options['preferredLanguage'] !== null) {
            $body['suggestedLanguage'] = $this->options['preferredLanguage'];
        }

        if ($this->options['reportUrl'] !== null) {
            $body['reportURL'] = $this->options['reportUrl'];
        }

        if ($this->options['cancelUrl'] !== null) {
            $body['cancelURL'] = $this->options['cancelUrl'];
        }

        if ($this->options['consumerIp'] !== null) {
            $body['consumerIP'] = $this->options['consumerIp'];
        }

        if ($this->options['amountChangeable']) {
            $body['paymentOptions']['inputAmountMin'] = $this->options['inputAmountMin'];
            $body['paymentOptions']['inputAmountMax'] = $this->options['inputAmountMax'];
        }

        if (!$this->options['amountChangeable']) {
            $body['paymentOptions']['inputAmount'] = $this->options['inputAmount'];
        }

        if (!empty($this->options['paymentMethods'])) {
            $body['paymentOptions']['paymentMethods'] = $this->options['paymentMethods'];
        }

        if ($this->options['sofortProductTypeId'] !== null) {
            $body['sofortProductTypeID'] = $this->options['sofortProductTypeId'];
        }

        if ($this->options['test'] !== null || $this->options['envionment'] === 1) {
            $body['test'] = $this->options['test'];
        }

        if ($this->options['acquirerPreprodTest'] !== null) {
            $body['acquirerPreprodTest'] = $this->options['acquirerPreprodTest'];
        }

        if ($this->options['tariffGroup'] !== null) {
            $body['tariffGroup'] = $this->options['tariffGroup'];
        }

        if (!empty($this->invoiceLines)) {
            $body['afterpayInvoiceLines'] = $this->invoiceLines;
        }

        $stream = stream_for(json_encode($body));

        return $this->withBody($stream);
    }

    /**
     * @param InvoiceLine $invoiceLine
     */
    private function withInvoiceLine(InvoiceLine $invoiceLine): void
    {
        if (!in_array($invoiceLine, $this->invoiceLines, true)) {
            $this->invoiceLines[] = $invoiceLine;
        }
    }

    /**
     * @param string $paymentMethod
     */
    private function withPaymentMethod(string $paymentMethod): void
    {
        $exists = in_array($paymentMethod, Payment::METHODS, true);
        $notAdded = !in_array($paymentMethod, $this->options['paymentMethods'], true);
        if ($exists && $notAdded) {
            $this->options['paymentMethods'][] = $paymentMethod;
        }
    }

    /**
     * @param array $options
     */
    public function withOptions(array $options): void
    {
        foreach ($options as $option => $value) {
            $this->withOption($option, $value);
        }
    }

    /**
     * @param string $option
     * @param $value
     * @return CreateTransaction
     */
    private function withOption(string $option, $value): self
    {
        if (array_key_exists($option, $this->options) && $this->options[$option] !== $value) {
            $this->options[$option] = $value;
        }

        return $this;
    }
}
