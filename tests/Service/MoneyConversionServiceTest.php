<?php

namespace App\Tests\Service;

use App\Entity\Dto\Money;
use App\Service\ExchangeRateProvider\Exception\TransactionException;
use App\Service\ExchangeRateProvider\ExchangeRateService;
use App\Service\MoneyConversionService;
use PHPUnit\Framework\TestCase;

class MoneyConversionServiceTest extends TestCase
{
    private ExchangeRateService $exchangeRateService;
    private MoneyConversionService $moneyConversionService;

    protected function setUp(): void
    {
        $this->exchangeRateService = $this->createMock(ExchangeRateService::class);
        $this->moneyConversionService = new MoneyConversionService($this->exchangeRateService);
    }

    public function testConvertRateIsNotReturnedShouldThrowError()
    {
        $this->exchangeRateService
            ->expects(self::once())
            ->method('conversionRates')
            ->will($this->throwException(new TransactionException(404, 'some error message')));

        $this->expectException(TransactionException::class);
        $this->moneyConversionService->convert('EUR', 'USD', 10);
    }

    public function testConvertRateIsReturnedShouldReturnMoney() {
        $this->exchangeRateService
            ->expects(self::once())
            ->method('conversionRates')
            ->willReturn(['USD' => 2]);

        $amount = $this->moneyConversionService->convert('EUR', 'USD', 10);

        $this->assertInstanceOf(Money::class, $amount);
        $this->assertEquals('EUR', $amount->getCurrency());
    }
}