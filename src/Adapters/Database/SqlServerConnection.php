<?php

namespace App\Adapters\Database;

require_once __DIR__ . '/../../Config/env.php';


use App\Domain\Database\ConnectionInterface;
use PDO;

loadEnv();

class SqlServerConnection implements ConnectionInterface
{
    public function getConnection(): PDO
    {
        return new PDO(
            "sqlsrv:Server=" . getenv('DB_HOST') . "," . getenv('DB_PORT') . ";Database=" . getenv('DB_NAME') . ";Encrypt=false;TrustServerCertificate=true",
            getenv('DB_USERNAME'),
            getenv('DB_PASSWORD'),
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
}
