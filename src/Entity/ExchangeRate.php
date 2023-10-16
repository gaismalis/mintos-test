<?php

namespace App\Entity;

use App\Entity\Dto\Money;
use App\Entity\Traits\TimestampableTrait;
use App\Repository\ExchangeRateRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExchangeRateRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ExchangeRate
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 3)]
    private ?string $baseCurrency = null;

    #[ORM\Column(length: 3)]
    private ?string $targetCurrency = null;

    #[ORM\Column]
    private ?float $exchangeRate = null;

    public function __construct(string $baseCurrency, string $targetCurrency, string $exchangeRate)
    {
        Money::validateCurrency($baseCurrency);
        Money::validateCurrency($targetCurrency);

        $this->baseCurrency = $baseCurrency;
        $this->targetCurrency = $targetCurrency;
        $this->exchangeRate = $exchangeRate;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getBaseCurrency(): ?string
    {
        return $this->baseCurrency;
    }

    public function setBaseCurrency(string $baseCurrency): static
    {
        $this->baseCurrency = $baseCurrency;

        return $this;
    }

    public function getTargetCurrency(): ?string
    {
        return $this->targetCurrency;
    }

    public function setTargetCurrency(string $targetCurrency): static
    {
        $this->targetCurrency = $targetCurrency;

        return $this;
    }

    public function getExchangeRate(): ?float
    {
        return $this->exchangeRate;
    }

    public function setExchangeRate(float $exchangeRate): static
    {
        $this->exchangeRate = $exchangeRate;

        return $this;
    }
}
