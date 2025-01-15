<?php

namespace App\Entity;

use App\Repository\DebitRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DebitRepository::class)]
class Debit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private ?string $no_account_involve = null;

    #[ORM\Column]
    private ?int $amount_debit = null;

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

    public function getAmountDebit(): ?int
    {
        return $this->amount_debit;
    }

    public function setAmountDebit(int $amount_debit): static
    {
        $this->amount_debit = $amount_debit;

        return $this;
    }
}
