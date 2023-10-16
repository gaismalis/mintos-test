<?php

namespace App\Service\ExchangeRateProvider;

interface ExchangeRateProvider
{
    public function conversionRates(): array;
}