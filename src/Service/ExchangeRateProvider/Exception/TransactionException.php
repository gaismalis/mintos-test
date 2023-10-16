<?php

namespace App\Service\ExchangeRateProvider\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class TransactionException extends HttpException
{
    public function __construct(int $statusCode, string $message = '')
    {
        parent::__construct($statusCode, $message, code: $statusCode);
    }
}