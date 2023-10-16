<?php

namespace App\Tests\Service;

use App\Entity\Account;
use App\Entity\Dto\Money;
use App\Repository\AccountRepository;
use App\Repository\TransactionRepository;
use App\Service\ExchangeRateProvider\Exception\TransactionException;
use App\Service\ExchangeRateProvider\ExchangeRateService;
use App\Service\MoneyConversionService;
use App\Service\TransactionService;
use App\Tests\Helpers\StubEntityFactory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class TransactionServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private MoneyConversionService $moneyConversionService;
    private TransactionService $transactionService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->moneyConversionService = $this->createMock(MoneyConversionService::class);
        $this->transactionService = new TransactionService($this->entityManager, $this->moneyConversionService);
    }

    public function testTransferValidationRecipientCurrencyMismatch() {
        $recipientAccount = StubEntityFactory::createAccount(amount: new Money('10', 'USD'));
        $senderAccount = StubEntityFactory::createAccount(amount: new Money('10', 'EUR'));

        $this->moneyConversionService
            ->expects(self::never())
            ->method('convert');

        $amount = new Money(10, 'EUR');

        $this->expectException(TransactionException::class);
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);
        $this->expectExceptionMessage(TransactionService::ERROR_CURRENCY_MISMATCH_RECIPIENT);

        $this->transactionService->transfer($recipientAccount, $senderAccount, $amount);
    }

    public function testTransferCurrencyConversionReturnsError() {
        $recipientAccount = StubEntityFactory::createAccount(amount: new Money('10', 'USD'));
        $senderAccount = StubEntityFactory::createAccount(amount: new Money('10', 'EUR'));
        $amount = new Money(10, 'USD');

        $this->moneyConversionService
            ->expects(self::once())
            ->method('convert')
            ->will($this->throwException(new TransactionException(400, 'some error')));

        $this->expectException(TransactionException::class);
        $this->transactionService->transfer($recipientAccount, $senderAccount, $amount);
    }

    public function testTransferValidationSenderInsufficientFunds() {
        $recipientAccount = StubEntityFactory::createAccount(amount: new Money('10', 'USD'));
        $senderAccount = StubEntityFactory::createAccount(amount: new Money('10', 'EUR'));
        $amount = new Money(10, 'USD');

        $this->moneyConversionService
            ->expects(self::once())
            ->method('convert')
            ->willReturn(new Money(15, 'EUR'));

        $this->expectException(TransactionException::class);
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);
        $this->expectExceptionMessage(TransactionService::ERROR_SENDER_INSUFFICIENT_FUNDS);

        $this->transactionService->transfer($recipientAccount, $senderAccount, $amount);
    }

    public function testTransactionSuccessful() {
        $recipientAccount = StubEntityFactory::createAccount(amount: new Money('10', 'USD'));
        $senderAccount = StubEntityFactory::createAccount(amount: new Money('10', 'EUR'));

        $recipientBalance = $recipientAccount->getBalance();
        $senderBalance = $senderAccount->getBalance();

        $amount = new Money(10, 'USD');

        $this->moneyConversionService
            ->expects(self::once())
            ->method('convert')
            ->willReturn(new Money(8, 'EUR'));

        $this->transactionService->transfer($recipientAccount, $senderAccount, $amount);

        $this->assertLessThan($senderBalance->getAmount(), $senderAccount->getBalance()->getAmount());
        $this->assertGreaterThan($recipientBalance->getAmount(), $recipientAccount->getBalance()->getAmount());
    }
}