<?php

require_once 'Config/env.php';

loadEnv();

$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '1433';
$user = getenv('DB_USERNAME') ?: 'sa';
$pass = getenv('DB_PASSWORD') ?: 'password';
$dbName = getenv('DB_NAME') ?: 'finch';

$dsnBase = "sqlsrv:Server=$host,$port;";
$params = "Encrypt=false;TrustServerCertificate=true";

$retries = 10;
$pdo = null;

while ($retries--) {
    try {
        $pdo = new PDO("{$dsnBase}Database=master;$params", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        break;
    } catch (PDOException $e) {
        echo "Aguardando SQL Server ficar disponível...\n";
        sleep(3);
    }
}

if (!$pdo) {
    echo "Erro: não foi possível conectar ao SQL Server.\n";
    exit(1);
}

echo "Conectado ao SQL Server. Verificando/criando banco {$dbName}...\n";

try {
    $pdo->exec("
        IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = {$dbName})
        BEGIN
            CREATE DATABASE{$dbName}
        END
    ");
} catch (PDOException $e) {
    echo "Erro ao criar banco: " . $e->getMessage() . "\n";
    exit(1);
}

try {
    $pdo = new PDO("{$dsnBase}Database{$dbName}$params", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erro ao conectar ao banco {$dbName}: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Criando tabelas se não existirem...\n";

try {
    $pdo->exec("
        IF OBJECT_ID('contas', 'U') IS NULL
        BEGIN
            CREATE TABLE contas (
                id INT PRIMARY KEY IDENTITY(1,1),
                nome NVARCHAR(255),
                saldo DECIMAL(18,2)
            );
        END
    ");

    $pdo->exec("
        IF OBJECT_ID('transacoes', 'U') IS NULL
        BEGIN
            CREATE TABLE transacoes (
                id INT PRIMARY KEY IDENTITY(1,1),
                conta_origem_id INT,
                conta_destino_id INT,
                valor DECIMAL(18,2),
                data_transferencia DATETIME DEFAULT GETDATE(),
                FOREIGN KEY (conta_origem_id) REFERENCES contas(id),
                FOREIGN KEY (conta_destino_id) REFERENCES contas(id)
            );
        END
    ");

    echo "Verificando se já existem contas...\n";

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM contas");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ((int)$row['total'] === 0) {
        echo "Inserindo contas iniciais...\n";
        $pdo->exec("
            INSERT INTO contas (nome, saldo) VALUES 
            ('Conta Teste 1', 1000.00),
            ('Conta teste 2', 2000.00);
        ");
    } else {
        echo "Contas já existentes. Nenhuma nova conta inserida.\n";
    }

    echo "Migração concluída com sucesso.\n";
} catch (PDOException $e) {
    echo "Erro ao criar tabelas ou inserir dados: " . $e->getMessage() . "\n";
    exit(1);
}
