<?php

namespace Digiwallet\Packages\Transaction\Client\Response;

use Psr\Http\Message\ResponseInterface;

/**
 * Class CreateTransaction
 * @package Digiwallet\Packages\Transaction\Client\Response
 */
class CreateTransaction implements CreateTransactionInterface
{
    /**
     * @var int
     */
    private $status;

    /**
     * @var string
     */
    private $message;

    /**
     * @var int
     */
    private $transactionId;

    /**
     * @var string
     */
    private $launchUrl;

    /**
     * @var string
     */
    private $transactionKey;


    private $response;

    /**
     * CreateTransaction constructor.
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $data = json_decode($response->getBody()->getContents(), true);

        $this->status = $data['status'];
        $this->message = $data['message'];
        $this->transactionId = $data['transactionID'];
        $this->launchUrl = $data['launchURL'];
        $this->transactionKey = $data['transactionKey'];
        $this->response = $data;
    }

    /**
     * @return int
     */
    public function status(): int
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function transactionId(): int
    {
        return $this->transactionId;
    }

    /**
     * @return string
     */
    public function launchUrl(): string
    {
        return $this->launchUrl;
    }

    /**
     * @return string
     */
    public function response(): array
    {
        return $this->response;
    }

    /**
     * @return string
     */
    public function transactionKey(): string
    {
        return $this->transactionKey;
    }
}
