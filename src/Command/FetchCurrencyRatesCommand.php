<?php

namespace App\Command;

use App\Entity\ExchangeRate;
use App\Service\ExchangeRateProvider\ExchangeRateService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:fetch-currency-rates',
    description: 'This method fetches currency rates daily to be used when API is not available',
)]
class FetchCurrencyRatesCommand extends Command
{
    private ExchangeRateService $exchangeRateService;
    private EntityManagerInterface $entityManager;
    public function __construct(
        ExchangeRateService $exchangeRateService,
        EntityManagerInterface $entityManager
    ) {
        $this->exchangeRateService = $exchangeRateService;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('baseCurrency', InputArgument::REQUIRED, 'Base currency')
            ->addArgument('targetCurrencies', InputArgument::REQUIRED, 'Target currencies')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $baseCurrency = $input->getArgument('baseCurrency');
        $targetCurrencies = $input->getArgument('targetCurrencies');

        $output->writeln('Importing currency rates for: ' . $baseCurrency);

        $rates = $this->exchangeRateService->conversionRates([
            'currencies' => $targetCurrencies,
            'base_currency' => $baseCurrency,
        ], false);

        foreach ($rates as $targetCurrency => $rate) {
            $output->writeln(sprintf('%s: %f', $targetCurrency, $rate));

            $exchangeRate = new ExchangeRate(
                $baseCurrency,
                $targetCurrency,
                $rate
            );

            $this->entityManager->persist($exchangeRate);
        }

        $this->entityManager->flush();

        $output->writeln('Done');

        return Command::SUCCESS;
    }
}
