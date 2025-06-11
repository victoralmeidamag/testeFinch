<?php

namespace App\Domain\Repositories;

interface RepositoryInterface
{
    public function beginTransaction(): void;
    public function commit(): void;
    public function rollback(): void;
}
