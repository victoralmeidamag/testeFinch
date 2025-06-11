<?php

namespace App\Domain\Entities;

class Account
{
    public function __construct(
        public int $id,
        public string $name,
        public float $balance
    ) {}

    public function debit(float $amount): void
    {
        if ($this->balance < $amount) {
            throw new \RuntimeException("Saldo insuficiente.");
        }
        $this->balance -= $amount;
    }

    public function credit(float $amount): void
    {
        $this->balance += $amount;
    }
}
