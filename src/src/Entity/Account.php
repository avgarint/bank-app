<?php

namespace App\Entity;

use App\Repository\AccountRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccountRepository::class)]
class Account
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column]
    private ?int $balance = null;

    #[ORM\Column(length: 10)]
    private ?string $number = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        if (!in_array($type, ['COURANT', 'EPARGNE'])) {
            throw new \InvalidArgumentException('Le type de compte doit être "COURANT" ou "EPARGNE".');
        }

        $this->type = $type;

        return $this;
    }

    public function getBalance(): ?int
    {
        return $this->balance;
    }

    public function setBalance(int $balance): static
    {
        if ($this->type === 'COURANT') {
            if ($balance < -400) {
                throw new \InvalidArgumentException('Un compte courant ne peut pas avoir un découvert inférieur à -400€.');
            }
        } elseif ($this->type === 'EPARGNE') {
            if ($balance < 0 || $balance > 25000) {
                throw new \InvalidArgumentException('Un compte épargne doit avoir un solde entre 0€ et 25 000€.');
            }
        } else {
            throw new \LogicException('Le type de compte doit être défini avant de définir le solde.');
        }

        $this->balance = $balance;

        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): static
    {
        if (!preg_match('/^\d{1,10}$/', $number)) {
            throw new \InvalidArgumentException('Le numéro de compte doit contenir uniquement des chiffres (jusqu’à 10 caractères).');
        }

        $this->number = $number;

        return $this;
    }
}
