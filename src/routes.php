<?php

function routes(string $uri, string $method): array|null
{
    return match ([$uri, $method]) {
        ['/transfer', 'POST'] => [App\Adapters\Controllers\TransferController::class, 'handle'],
        ['/stress', 'GET'] => [App\Adapters\Controllers\StressController::class, 'handle'],
        default => null,
    };
}
