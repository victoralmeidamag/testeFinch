<?php

namespace App\Adapters\Persistence;


use App\Domain\Entities\Account;
use App\Domain\Repositories\Account\AccountRepositoryInterface;
use PDO;
use RuntimeException;

class AccountRepositorySql implements AccountRepositoryInterface
{

    public function __construct(
        private PDO $pdo
    ) {}


    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollback(): void
    {
        $this->pdo->rollBack();
    }

    public function findById(int $id): Account
    {
        $stmt = $this->pdo->prepare("SELECT id, nome, saldo FROM contas WHERE id = ? FOR UPDATE");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new RuntimeException("Conta {$id} não encontrada.");
        }

        return new Account((int)$row['id'], $row['nome'], (float)$row['saldo']);
    }

    public function save(Account $account): void
    {
        $stmt = $this->pdo->prepare("UPDATE contas SET saldo = ? WHERE id = ?");
        $stmt->execute([$account->balance, $account->id]);
    }

    public function findAndLock(int $id): Account
    {
        $stmt = $this->pdo->prepare("SELECT id, nome, saldo FROM contas WITH (UPDLOCK) WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new RuntimeException("Conta {$id} não encontrada.");
        }

        return new Account((int)$row['id'], $row['nome'], (float)$row['saldo']);
    }

    public function logTransaction(int $fromId, int $toId, float $amount): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO transacoes (conta_origem_id, conta_destino_id, valor) VALUES (?, ?, ?)"
        );
        $stmt->execute([$fromId, $toId, $amount]);
    }
}

