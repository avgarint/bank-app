<?php

namespace App\Entity;

use App\Repository\DepositRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DepositRepository::class)]
class Deposit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private ?string $no_account_involve = null;

    #[ORM\Column]
    private ?int $amount = null;

    #[ORM\Column]
    private ?int $id_account = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNoAccountInvolve(): ?string
    {
        return $this->no_account_involve;
    }

    public function setNoAccountInvolve(string $no_account_involve): static
    {
        $this->no_account_involve = $no_account_involve;

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

    public function getIdAccount(): ?int
    {
        return $this->id_account;
    }

    public function setIdAccount(int $id_account): static
    {
        $this->id_account = $id_account;

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
