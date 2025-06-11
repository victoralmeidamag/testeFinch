<?php

namespace App\Domain\Database;

use PDO;

interface ConnectionInterface
{
    public function getConnection(): PDO;
}
