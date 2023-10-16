<?php

namespace App\Entity;

use App\Entity\Dto\Money;
use App\Entity\Traits\TimestampableTrait;
use App\Repository\AccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity(repositoryClass: AccountRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Account
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'accounts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Ignore]
    private Client $client;

    #[ORM\Embedded(class: Money::class, columnPrefix: 'balance_')]
    private ?Money $balance = null;

    #[ORM\OneToMany(mappedBy: 'account', targetEntity: Transaction::class, orphanRemoval: true)]
    #[Ignore]
    private Collection $transactions;

    public function __construct(Client $client, Money $balance)
    {
        $this->transactions = new ArrayCollection();
        $this->client = $client;
        $this->balance = $balance;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function client(): Client
    {
        return $this->client;
    }

    public function getBalance(): Money
    {
        return $this->balance;
    }

    public function setBalance(Money $balance): static
    {
        $this->balance = $balance;

        return $this;
    }
}
