<?php

namespace App\Entity;

use App\Repository\TransferRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TransferRepository::class)]
class Transfer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private ?string $no_account_emitter = null;

    #[ORM\Column(length: 10)]
    private ?string $no_account_receiver = null;

    #[ORM\Column]
    #[Assert\Positive(message: 'Le montant doit être un nombre positif.')] // Validation ajoutée ici
    private ?int $amount = null;

    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Account $account = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNoAccountEmitter(): ?string
    {
        return $this->no_account_emitter;
    }

    public function setNoAccountEmitter(string $no_account_emitter): static
    {
        $this->no_account_emitter = $no_account_emitter;

        return $this;
    }

    public function getNoAccountReceiver(): ?string
    {
        return $this->no_account_receiver;
    }

    public function setNoAccountReceiver(string $no_account_receiver): static
    {
        $this->no_account_receiver = $no_account_receiver;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): static
    {
        $this->account = $account;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }
}
