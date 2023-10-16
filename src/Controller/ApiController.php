<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Client;
use App\Entity\Dto\Money;
use App\Repository\AccountRepository;
use App\Repository\ClientRepository;
use App\Repository\TransactionRepository;
use App\Service\ExchangeRateProvider\Exception\TransactionException;
use App\Service\TransactionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ApiController extends AbstractController
{
    private SerializerInterface $serializer;
    private TransactionService $transactionService;
    private AccountRepository $accountRepository;
    private ClientRepository $clientRepository;
    private TransactionRepository $transactionRepository;

    public function __construct(
        SerializerInterface $serializer,
        TransactionService $transactionService,
        AccountRepository $accountRepository,
        ClientRepository $clientRepository,
        TransactionRepository $transactionRepository
    ) {
        $this->serializer = $serializer;
        $this->transactionService = $transactionService;
        $this->accountRepository = $accountRepository;
        $this->clientRepository = $clientRepository;
        $this->transactionRepository = $transactionRepository;
    }

    //todo: should I add rate limiter? https://symfony.com/doc/current/rate_limiter.html
    #[Route('/v1/clients/{clientId}/accounts', name: 'client_accounts')]
    public function accounts(int $clientId): JsonResponse
    {
        /** @var Client $client */
        $client = $this->clientRepository->find($clientId);

        if (! $client) {
            return new JsonResponse([
                'error' => 'Client not found',
                'message' => 'Client with provided ID does not exist'
            ],
            HttpResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse(
            $this->serializer->normalize($client->getAccounts(), 'json'),
            HttpResponse::HTTP_OK
        );
    }

    #[Route('/v1/accounts/{accountId}/transactions', name: 'account_transactions')]
    public function transactions(Request $request, int $accountId): JsonResponse
    {
        $limit = $request->query->get('limit');
        $offset = $request->query->get('offset');

        /** @var Account $account */
        $account = $this->accountRepository->find($accountId);

        if (! $account) {
            return new JsonResponse(
                [
                    'error' => 'Account not found',
                    'message' => 'Account with provided ID does not exist'
                ],
                HttpResponse::HTTP_NOT_FOUND,
            );
        }

        return new JsonResponse(
            $this->serializer->normalize($this->transactionRepository->findBy(['account' => $accountId], ['id' => 'DESC'], $limit, $offset), 'json'),
            HttpResponse::HTTP_OK,
        );
    }

    #[Route('/v1/accounts/transfer', name: 'money_transfer', methods: ['POST'])]
    public function transfer(Request $request): JsonResponse
    {
        $recipientAccountId = $request->request->get('recipientAccountId');
        $senderAccountId = $request->request->get('senderAccountId');
        $amount = $request->request->get('amount');
        $currency = $request->request->get('currency');

        $recipientAccount = $this->accountRepository->find($recipientAccountId);
        $senderAccount = $this->accountRepository->find($senderAccountId);

        $amount = new Money($amount, $currency);

        try {
            if (! $recipientAccount || ! $senderAccount) {
                throw new TransactionException('Recipient or sender account not found', HttpResponse::HTTP_NOT_FOUND);
            }

            $this->transactionService->transfer($recipientAccount, $senderAccount, $amount);
        } catch (TransactionException $exception) {
            return new JsonResponse(['message' => $exception->getMessage()], $exception->getStatusCode());
        }

        return new JsonResponse(['message' => 'Money transferred successfully'], HttpResponse::HTTP_OK);
    }
}
