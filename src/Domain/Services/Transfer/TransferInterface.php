<?php

namespace App\Domain\Services\Transfer;

use App\Domain\Entities\Account;

interface TransferInterface
{
    public function execute(Account $from, Account $to, float $amount): void;
}
