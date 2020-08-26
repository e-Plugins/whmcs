<?php
namespace Digiwallet\Packages\Transaction\Client;

use Digiwallet\Packages\Transaction\Client\Request\CheckTransactionInterface as CheckTransactionRequest;
use Digiwallet\Packages\Transaction\Client\Request\CreateTransactionInterface as CreateTransactionRequest;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Interface ClientInterface
 * @package Digiwallet\Packages\Transaction\Client
 */
interface ClientInterface extends \GuzzleHttp\ClientInterface
{
    /**
     * @param CreateTransactionRequest $request
     * @return Response
     * @throws GuzzleException
     */
    public function createTransaction(CreateTransactionRequest $request): Response;

    /**
     * @param CheckTransactionRequest $request
     * @return Response
     * @throws GuzzleException
     */
    public function checkTransaction(CheckTransactionRequest $request): Response;
}
