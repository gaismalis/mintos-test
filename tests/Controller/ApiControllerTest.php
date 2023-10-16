<?php

namespace App\Tests\Controller;

use App\Controller\ApiController;
use App\Entity\Account;
use App\Entity\Client;
use App\Repository\AccountRepository;
use App\Repository\ClientRepository;
use App\Repository\TransactionRepository;
use App\Service\ExchangeRateProvider\Exception\TransactionException;
use App\Service\TransactionService;
use App\Tests\Helpers\StubEntityFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\Serializer\SerializerInterface;

class ApiControllerTest extends TestCase
{
    private $serializer;
    private $transactionService;
    private $accountRepository;
    private $clientRepository;
    private $transactionRepository;
    private $apiController;

    protected function setUp(): void
    {
        $this->serializer = $this->getMockBuilder(SerializerInterface::class)
            ->onlyMethods(['serialize', 'deserialize']) // this seems to be needed because its abstract clas
            ->addMethods(['normalize'])
            ->getMock();
        $this->transactionService = $this->createMock(TransactionService::class);
        $this->clientRepository = $this->createMock(ClientRepository::class);
        $this->accountRepository = $this->createMock(AccountRepository::class);
        $this->transactionRepository = $this->createMock(TransactionRepository::class);

        $this->apiController = new ApiController(
            $this->serializer,
            $this->transactionService,
            $this->accountRepository,
            $this->clientRepository,
            $this->transactionRepository
        );
    }

    public function testAccountsUserNotFoundResponse() {
        $this->clientRepository
            ->expects(self::once())
            ->method('find')
            ->willReturn(null);

        $response = $this->apiController->accounts(1);

        $this->assertEquals(HttpResponse::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testAccountsUserFoundResponse() {
        $this->clientRepository
            ->expects(self::once())
            ->method('find')
            ->willReturn(new Client());

        // I guess should create a normal response not empty array
        $this->serializer
            ->expects(self::once())
            ->method('normalize')
            ->willReturn([]);

        $response = $this->apiController->accounts(1);

        $this->assertEquals(HttpResponse::HTTP_OK, $response->getStatusCode());
    }

    public function testTransactionsAccountNotFound() {
        $this->accountRepository
            ->expects(self::once())
            ->method('find')
            ->willReturn(null);

        $this->serializer
            ->expects(self::never())
            ->method('normalize');

        $response = $this->apiController->transactions(new Request(), 1);

        $this->assertEquals(HttpResponse::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testTransactionsAccountFoundResponse() {
        $this->accountRepository
            ->expects(self::once())
            ->method('find')
            ->willReturn(StubEntityFactory::createAccount());

        $this->serializer
            ->expects(self::once())
            ->method('normalize')
            ->willReturn([]);

        $query = [
            'limit' => 10,
            'offset' => 10,
        ];

        $response = $this->apiController->transactions(new Request(query: $query), 1);

        $this->assertEquals(HttpResponse::HTTP_OK, $response->getStatusCode());
    }

    public function testTransferErrorInService() {
        $this->transactionService
            ->expects(self::once())
            ->method('transfer')
            ->willThrowException(new TransactionException(400,TransactionService::ERROR_CURRENCY_MISMATCH_RECIPIENT));

        $this->accountRepository
            ->expects(self::exactly(2))
            ->method('find')
            ->willReturn(StubEntityFactory::createAccount());

        $params = [
            'recipientAccountId' => 1,
            'senderAccountId' => 1,
            'amount' => 1,
            'currency' => 'EUR'
        ];
        $response = $this->apiController->transfer(new Request(request: $params));

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testTransferSuccessful() {
        $this->transactionService
            ->expects(self::once())
            ->method('transfer');

        $this->accountRepository
            ->expects(self::exactly(2))
            ->method('find')
            ->willReturn(StubEntityFactory::createAccount());

        $params = [
            'recipientAccountId' => 1,
            'senderAccountId' => 2,
            'amount' => 1,
            'currency' => 'EUR'
        ];
        $response = $this->apiController->transfer(new Request(request: $params));

        $this->assertEquals(200, $response->getStatusCode());
    }
}