<?php
namespace Digiwallet\Packages\Transaction\Client\Request;

use Digiwallet\Packages\Transaction\Client\ClientInterface as TransactionClient;
use Digiwallet\Packages\Transaction\Client\Response\CheckTransactionInterface as CheckTransactionResponseInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Interface TransactionRequest
 * @package Digiwallet\Packages\Transaction\Client\Request
 */
interface CheckTransactionInterface extends RequestInterface
{
    /**
     * @return CheckTransactionInterface
     */
    public function enableTestMode(): self;

    /**
     * @param int $outletId
     * @return CheckTransactionInterface
     */
    public function withOutlet(int $outletId): self;

    /**
     * @param string $bearer
     * @return CheckTransactionInterface
     */
    public function withBearer(string $bearer): self;

    /**
     * @param int $transactionId
     * @return CheckTransactionInterface
     */
    public function withTransactionId(int $transactionId): self;

    /**
     * @return CheckTransactionResponseInterface
     */
    public function send(): CheckTransactionResponseInterface;
}
