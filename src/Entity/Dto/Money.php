<?php

namespace App\Entity\Dto;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpFoundation\Response;

#[ORM\Embeddable]
class Money
{
    #[ORM\Column(type: "decimal", precision: 15, scale: 2)]
    private float $amount;

    #[ORM\Column(type: "string")]
    private string $currency;

    const ERROR_INVALID_CURRENCY = "Not a valid currency";
    public const VALID_CURRENCIES = [
        'USD',
        'EUR',
        'GBP',
    ];

    public function __construct(float $amount, string $currency)
    {
        self::validateCurrency($currency);

        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount($amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency($currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    //TODO: not good, need to rework - symfony validator?
    public static function validateCurrency(string $currency) {
        if (!in_array($currency, self::VALID_CURRENCIES)) {
            throw new \Exception(self::ERROR_INVALID_CURRENCY, Response::HTTP_BAD_REQUEST);
        }
    }
}