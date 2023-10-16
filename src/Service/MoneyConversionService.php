<?php

namespace App\Service;

use App\Entity\Dto\Money;
use App\Service\ExchangeRateProvider\ExchangeRateService;

class MoneyConversionService
{
    private ExchangeRateService $exchangeRateService;

    public function __construct(ExchangeRateService $exchangeRateService)
    {
        $this->exchangeRateService = $exchangeRateService;
    }

    public function convert(string $baseCurrency, string $targetCurrency, float $targetAmount): Money {
        $query = [
            'currencies' => $targetCurrency,
            'base_currency' => $baseCurrency,
        ];
        $rate = $this->exchangeRateService->conversionRates($query)[$targetCurrency];

        return new Money($targetAmount / $rate, $baseCurrency);
    }
}