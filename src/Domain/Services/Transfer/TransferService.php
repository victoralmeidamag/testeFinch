<?php

namespace App\Domain\Services\Transfer;

use App\Domain\Entities\Account;
use App\Domain\Services\Transfer\TransferInterface;


class TransferService implements TransferInterface
{
    public function execute(Account $from, Account $to, float $amount): void
    {
        $from->debit($amount);
        $to->credit($amount);
    }
}
