<?php

function routes(string $uri, string $method): array|null
{
    return match ([$uri, $method]) {
        ['/transfer', 'POST'] => [App\Adapters\Controllers\TransferController::class, 'handle'],
        default => null,
    };
}
