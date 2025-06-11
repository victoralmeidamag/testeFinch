<?php

use App\Adapters\Controllers\TransferController;
use App\Application\UseCases\TransferUseCase;
use App\Domain\Services\Transfer\TransferService;
use App\Adapters\Persistence\AccountRepositorySql;

function dependencies(string $class): ?callable
{
    return match ($class) {
        TransferController::class => function (PDO $pdo) {
            $repo = new AccountRepositorySql($pdo);
            $service = new TransferService();
            $useCase = new TransferUseCase($repo, $service);
            return new TransferController($useCase);
        },
        default => null
    };
}
