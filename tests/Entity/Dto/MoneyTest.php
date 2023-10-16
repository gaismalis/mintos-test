<?php

namespace App\Tests\Entity\Dto;

use App\Entity\Dto\Money;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class MoneyTest extends TestCase
{
    public function testInvalidCurrencyShouldThrowError() {

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);
        $this->expectExceptionMessage(Money::ERROR_INVALID_CURRENCY);

        new Money('10', 'XXX');
    }

    public function testSetAmount() {
        $money = new Money(0, 'EUR');

        $money->setAmount(10);
        $this->assertEquals(10, $money->getAmount());

    }

   public function testSetCurrency() {
       $money = new Money(0, 'EUR');

       $money->setCurrency('USD');
       $this->assertEquals('USD', $money->getCurrency());
    }
}