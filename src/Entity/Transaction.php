<?php

namespace App\Entity;

use App\Entity\Dto\Money;
use App\Entity\Enum\TransactionType;
use App\Entity\Traits\TimestampableTrait;
use App\Repository\TransactionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Transaction
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    //todo can make these accounts embedded
    #[ORM\ManyToOne(inversedBy: 'transactions', fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: false)]
    private Account $account;

    #[ORM\Embedded(class: Money::class, columnPrefix: 'amount_')]
    private Money $amount;

    #[ORM\Column(type: 'string', nullable: false, length: 100)]
    private string $type;

    public function __construct(
        Account $account,
        Money   $amount,
        TransactionType $type
    ) {
        $this->account = $account;
        $this->amount = $amount;
        $this->type = $type->value;
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

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function status(): TransactionType
    {
        return TransactionType::from($this->type);
    }
}
