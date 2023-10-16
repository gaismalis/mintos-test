<?php

namespace App\Service\ExchangeRateProvider;

use App\Entity\Dto\Money;
use App\Repository\ExchangeRateRepository;
use App\Service\ExchangeRateProvider\Exception\TransactionException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpFoundation\Response;

class ExchangeRateService implements ExchangeRateProvider
{
    public const ERROR_FALLBACK_RATES_NOT_FOUND = 'Fallback rate from database not found';

    private const MAX_RETRIES = 3;
    private const TIMEOUT = 5;

    private Client $httpClient;
    private ExchangeRateRepository $exchangeRateRepository;
    private string $apiKey;
    private string $baseUrl;

    public function __construct(Client $httpClient, ExchangeRateRepository $exchangeRateRepository, string $baseUrl, string $apiKey) {
        $this->httpClient = $httpClient;
        $this->exchangeRateRepository = $exchangeRateRepository;
        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl;
    }

    private function call(string $endpoint, ?array $query = []) {
        $retryCount = 0;
        $url = $this->baseUrl . $endpoint;
        $query['apikey'] = $this->apiKey;

        while ($retryCount< self::MAX_RETRIES) {
            try {
                $response = $this->httpClient->request('GET', $url, ['query' => $query]);

                return json_decode($response->getBody(), true);
            } catch (GuzzleException|Exception $exception) {
                if ($exception->getCode() === Response::HTTP_TOO_MANY_REQUESTS) {
                    sleep(self::TIMEOUT);
                    $retryCount++;

                    continue;
                }

                throw new TransactionException($exception->getCode(), $exception->getMessage());
            }
        }
    }

    /** $useStaleRate argument determinates weather system will try to get rates from db in case Api is not available
     *  needed for daily crons that populate db with rates
     */
    public function conversionRates(array $query = [], bool $useStaleRate = true): array {
        try {
            $result =  $this->call('latest', $query);

            $rate =  $result['data'];
        } catch (TransactionException $exception) {
            if (! $useStaleRate) {
                throw $exception;
            }
            // should log something to inform devs/admins that api is unavailable

            $rate = $this->exchangeRateRepository->findOneBy([
                'baseCurrency' => $query['base_currency'],
                'targetCurrency' => $query['currencies'],
            ],
            [
                'createdAt' => 'DESC'
            ]);

            if (! $rate) {
                throw new TransactionException(Response::HTTP_NOT_FOUND, self::ERROR_FALLBACK_RATES_NOT_FOUND);
            }

            $rate = [$rate->getTargetCurrency() => $rate->getExchangeRate()];
        }

        return  $rate;
    }
}