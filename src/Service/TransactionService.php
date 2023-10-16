<?php

namespace App\Service;

use App\Entity\Account;
use App\Entity\Dto\Money;
use App\Entity\Enum\TransactionType;
use App\Entity\Transaction;
use App\Service\ExchangeRateProvider\Exception\TransactionException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class TransactionService
{
    const ERROR_CURRENCY_MISMATCH_RECIPIENT = "Currency not matching recipient account";
    const ERROR_SENDER_INSUFFICIENT_FUNDS = "Not enough funds in account";

    private EntityManagerInterface $entityManager;
    private MoneyConversionService $moneyConversionService;

    public function __construct(
        EntityManagerInterface $entityManager,
        MoneyConversionService $moneyConversionService
    )
    {
        $this->entityManager = $entityManager;
        $this->moneyConversionService = $moneyConversionService;
    }

    public function transfer(
        Account $recipientAccount,
        Account $senderAccount,
        Money $amount,
    ) : void
    {
        $this->validate($recipientAccount, $amount);

        $senderAmount = $this->senderCurrencyConversion($senderAccount, $amount);

        $this->transferMoney($recipientAccount, $senderAccount, $amount, $senderAmount);
    }

    private function validate(Account $recipientAccount, Money $amount):void
    {
        if (! ($recipientAccount->getBalance()->getCurrency() === $amount->getCurrency())) {
            throw new TransactionException(Response::HTTP_BAD_REQUEST, self::ERROR_CURRENCY_MISMATCH_RECIPIENT);
        }
    }

    private function senderCurrencyConversion(Account $senderAccount, Money $amount): Money
    {
        $senderAmount = $amount;

        if (! ($senderAccount->getBalance()->getCurrency() === $amount->getCurrency())) {
            $senderAmount = $this->moneyConversionService->convert(
                $senderAccount->getBalance()->getCurrency(),
                $amount->getCurrency(),
                $amount->getAmount()
            );
        }

        if ($senderAccount->getBalance()->getAmount() < $senderAmount->getAmount()) {
            throw new TransactionException(Response::HTTP_BAD_REQUEST, self::ERROR_SENDER_INSUFFICIENT_FUNDS);
        }

        return $senderAmount;
    }

    private function transferMoney(Account $recipientAccount, Account $senderAccount, Money $amount, Money $senderAmount): void
    {
        try {
            $this->entityManager->beginTransaction();

            $this->updateSenderBalance($senderAccount, $senderAmount);
            $this->updateRecipientBalance($recipientAccount, $amount);
            $this->createTransactionRecords($recipientAccount, $senderAccount, $amount,  $senderAmount);

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $exception) {
            $this->entityManager->rollback();

            throw new TransactionException($exception->getMessage(), $exception->getCode());
        }
    }

    private function updateSenderBalance(Account $senderAccount, Money $amount): void
    {
        $senderBalance = $senderAccount->getBalance()->getAmount();
        $newBalance = new Money(
            $senderBalance - $amount->getAmount(),
            $senderAccount->getBalance()->getCurrency()
        );
        $senderAccount->setBalance($newBalance);
        $this->entityManager->persist($senderAccount);
    }

    private function updateRecipientBalance(Account $recipientAccount, Money $amount): void
    {
        $recipientBalance = $recipientAccount->getBalance()->getAmount();
        $newBalance = new Money(
            $recipientBalance + $amount->getAmount(),
            $recipientAccount->getBalance()->getCurrency()
        );
        $recipientAccount->setBalance($newBalance);
        $this->entityManager->persist($recipientAccount);
    }

    private function createTransactionRecords(
        Account $recipientAccount,
        Account $senderAccount,
        Money $recipientAmount,
        Money $senderAmount
    ): void {
        $senderTransaction = new Transaction($senderAccount, $senderAmount, TransactionType::OUTGOING);
        $recipientTransaction = new Transaction($recipientAccount, $recipientAmount, TransactionType::INCOMING);

        $this->entityManager->persist($senderTransaction);
        $this->entityManager->persist($recipientTransaction);
    }

}