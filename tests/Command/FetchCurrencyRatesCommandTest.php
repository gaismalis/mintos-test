<?php

namespace App\Tests\Command;

use App\Command\FetchCurrencyRatesCommand;
use App\Service\ExchangeRateProvider\Exception\TransactionException;
use App\Service\ExchangeRateProvider\ExchangeRateService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;


class FetchCurrencyRatesCommandTest extends KernelTestCase
{
    private CommandTester $commandTester;
    private EntityManagerInterface $entityManager;
    private ExchangeRateService $exchangeRateService;


    public function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->exchangeRateService = $this->createMock(ExchangeRateService::class);

        $command = new FetchCurrencyRatesCommand($this->exchangeRateService, $this->entityManager);
        $this->commandTester = new CommandTester($command);
    }

    public function testHttpClientReturnsError()
    {
        $this->exchangeRateService
            ->expects(self::once())
            ->method('conversionRates')
            ->will($this->throwException(new TransactionException(400, 'error message test')));

        $this->entityManager
            ->expects(self::never())
            ->method('persist');


        $this->entityManager
            ->expects(self::never())
            ->method('flush');

        $this->expectException(TransactionException::class);
        $this->commandTester->execute(['targetCurrencies' => 'EUR, GBP', 'baseCurrency' => 'USD']);
    }

    public function testHttpClientReturnsRates()
    {
        $this->exchangeRateService
            ->expects(self::once())
            ->method('conversionRates')
            ->willReturn([
                'EUR' => 1.1,
                'GBP' => 1.2
            ]);

        $this->entityManager
            ->expects(self::exactly(2))
            ->method('persist');


        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->commandTester->execute(['targetCurrencies' => 'EUR, GBP', 'baseCurrency' => 'USD']);

        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString('Importing currency rates for: USD', $output);
        $this->assertStringContainsString('Done', $output);
    }


}