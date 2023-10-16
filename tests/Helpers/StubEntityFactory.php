<?php

namespace App\Tests\Helpers;

use App\Entity\Account;
use App\Entity\Client;
use App\Entity\Dto\Money;

class StubEntityFactory
{
    public static function createAccount(Client $client = null, Money $amount = null) {
        if(! $client) {
            $client = new Client();
        }

        if(! $amount) {
            $amount = new Money(10, 'EUR');
        }

        return new Account($client, $amount);
    }
}