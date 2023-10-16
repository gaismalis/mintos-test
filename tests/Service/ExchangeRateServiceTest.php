<?php

namespace App\Tests\Service;

use App\Entity\ExchangeRate;
use App\Repository\ExchangeRateRepository;
use App\Service\ExchangeRateProvider\Exception\TransactionException;
use App\Service\ExchangeRateProvider\ExchangeRateService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ExchangeRateServiceTest extends TestCase
{
    private ExchangeRateService $exchangeRateService;
    private ExchangeRateRepository $exchangeRateRepository;
    private $query;

    protected function setUp(): void
    {
        $this->query = [
            'currencies' => 'EUR',
            'base_currency' => 'USD',
        ];
        $this->exchangeRateRepository = $this->createMock(ExchangeRateRepository::class);
    }

    public function testRequestSuccessfulReturnRate() {
        $jsonData = json_encode([
            "data" => [
                "EUR" => 0.9516801048
            ]
        ]);
        $mockedResponse = new Response(200, body: $jsonData);

        $this->createClientMock($mockedResponse);

        $this->exchangeRateRepository
            ->expects(self::never())
            ->method('findOneBy');

        $rate = $this->exchangeRateService->conversionRates($this->query);
        $this->assertIsArray($rate);
        $this->assertArrayHasKey('EUR', $rate);
        $this->assertEquals(0.9516801048, $rate['EUR']);
    }


    public function testRequestErrorReturnStaleRate() {
        $this->createClientMock(new TransferException('Some client error', 400));

        $this->exchangeRateRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->willReturn(new ExchangeRate('USD', 'EUR', 0.5));

        $result = $this->exchangeRateService->conversionRates($this->query);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('EUR', $result);
        $this->assertEquals(0.5, $result['EUR']);
    }

    public function testRequestErrorCantFindStaleRate() {
        $this->createClientMock(new TransferException('Some client error', 400));

        $this->exchangeRateRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->willReturn(null);

        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage(ExchangeRateService::ERROR_FALLBACK_RATES_NOT_FOUND);
        $this->expectExceptionCode(\Symfony\Component\HttpFoundation\Response::HTTP_NOT_FOUND);
        $this->exchangeRateService->conversionRates($this->query);
    }

    public function testRequestErrorAndDontUseStaleRate() {
        $this->createClientMock(new TransferException('Some client error', 400));

        $this->exchangeRateRepository
            ->expects(self::never())
            ->method('findOneBy')
            ->willReturn(null);

        $this->expectException(TransactionException::class);
        $this->exchangeRateService->conversionRates($this->query, false);
    }

    private function createClientMock($mockedResponse) {
        $mock = new MockHandler([$mockedResponse]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->exchangeRateService = new ExchangeRateService(
            $client,
            $this->exchangeRateRepository,
            'base-url',
            'api-key'
        );
    }
}