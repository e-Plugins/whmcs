<?php

namespace Digiwallet\Packages\Transaction\Client\Response;

use Psr\Http\Message\ResponseInterface;

/**
 * Class CheckTransaction
 * @package Digiwallet\Packages\Transaction\Client\Response
 */
class CheckTransaction implements CheckTransactionInterface
{
    /**
     * @var int
     */
    private $status;

    /**
     * @var string
     */
    private $transactionStatus;

    /**
     * @var int
     */
    private $externalTransactionID;

    /**
     * @var int
     */
    private $paidAmount;

    /**
     * @var string
     */
    private $externalPaymentReference;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $consumerName;

    /**
     * @var string
     */
    private $consumerIBAN;

    /**
     * @var string
     */
    private $consumerCountryCode;

    /**
     * @var string
     */
    private $consumerCardNumberMasked;

    /**
     * @var string
     */
    private $consumerCardExpiryDate;

    /**
     * @var string
     */
    private $consumerCardBrand;
    /**
     * @var string(3)
     */
    private $paymentMethodCode;

    /**
     * CreateTransaction constructor.
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $data = json_decode($response->getBody()->getContents(), true);

        $this->status = $data['status'];
        $this->message = $data['message'];
        $this->transactionStatus = $data['transactionStatus'];
        $this->externalTransactionID = $data['externalTransactionID'] ?? null;
        $this->externalPaymentReference = $data['externalPaymentReference'] ?? null;
        $this->paymentMethodCode = $data['paymentMethodCode'] ?? null;
        $this->paidAmount = $data['paidAmount'] ?? null;
        $this->consumerName = $data['consumerName'] ?? null;
        $this->consumerIBAN = $data['consumerIBAN'] ?? null;
        $this->consumerCountryCode = $data['consumerCountryCode'] ?? null;
        $this->consumerCardNumberMasked = $data['consumerCardNumberMasked'] ?? null;
        $this->consumerCardExpiryDate = $data['consumerCardExpiryDate'] ?? null;
        $this->consumerCardBrand = $data['consumerCardBrand'] ?? null;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getTransactionStatus(): string
    {
        return $this->transactionStatus;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string|null
     */
    public function consumerName(): ?string
    {
        return $this->consumerName;
    }

    /**
     * @return string|null
     */
    public function consumerIBAN(): ?string
    {
        return $this->consumerIBAN;
    }

    /**
     * @return string|null
     */
    public function consumerCountryCode(): ?string
    {
        return $this->consumerCountryCode;
    }

    /**
     * @return string|null
     */
    public function consumerCardNumberMasked(): ?string
    {
        return $this->consumerCardNumberMasked;
    }

    /**
     * @return string|null
     */
    public function consumerCardExpiryDate(): ?string
    {
        return $this->consumerCardExpiryDate;
    }

    /**
     * @return string|null
     */
    public function consumerCardBrand(): ?string
    {
        return $this->consumerCardBrand;
    }

    /**
     * @return int
     */
    public function getExternalTransactionID(): int
    {
        return $this->externalTransactionID;
    }

    /**
     * @return int
     *
     * the amount paid in cents
     */
    public function getPaidAmount(): int
    {
        return $this->paidAmount;
    }

    /**
     * @return string
     */
    public function getExternalPaymentReference(): ?string
    {
        return $this->externalPaymentReference;
    }

    /**
     * @return string(3)
     */
    public function getPaymentMethodCode(): string
    {
        return $this->paymentMethodCode;
    }
}
