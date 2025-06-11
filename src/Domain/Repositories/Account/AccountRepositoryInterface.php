<?php

namespace App\Domain\Repositories\Account;

use App\Domain\Entities\Account;
use App\Domain\Repositories\RepositoryInterface;

interface AccountRepositoryInterface extends RepositoryInterface
{
    public function findById(int $id): Account;

    public function save(Account $account): void;

    public function findAndLock(int $id): Account;

    public function logTransaction(int $fromId, int $toId, float $amount): void;
}
