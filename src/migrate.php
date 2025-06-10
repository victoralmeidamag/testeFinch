<?php

$host = 'sqlserver';
$port = '1433';
$user = 'sa';
$pass = 'testeFinch@1';

// parâmetros de conexão comuns para evitar erro de SSL
$dsnBase = "sqlsrv:Server=$host,$port;";
$params = "Encrypt=false;TrustServerCertificate=true";

$retries = 10;
$pdo = null;

while ($retries--) {
    try {
        // Conecta ao banco master
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

echo "Conectado ao SQL Server. Verificando/criando banco 'imi'...\n";

try {
    $pdo->exec("
        IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = 'imi')
        BEGIN
            CREATE DATABASE imi;
        END
    ");
} catch (PDOException $e) {
    echo "Erro ao criar banco: " . $e->getMessage() . "\n";
    exit(1);
}

try {
    $pdo = new PDO("{$dsnBase}Database=imi;$params", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Criando tabelas se não existirem...\n";

    $pdo->exec("
        IF OBJECT_ID('contas', 'U') IS NULL
        CREATE TABLE contas (
            id INT PRIMARY KEY IDENTITY(1,1),
            nome NVARCHAR(255),
            saldo DECIMAL(18,2)
        );

        IF OBJECT_ID('transacoes', 'U') IS NULL
        CREATE TABLE transacoes (
            id INT PRIMARY KEY IDENTITY(1,1),
            conta_origem_id INT,
            conta_destino_id INT,
            valor DECIMAL(18,2),
            data_transferencia DATETIME DEFAULT GETDATE(),
            FOREIGN KEY (conta_origem_id) REFERENCES contas(id),
            FOREIGN KEY (conta_destino_id) REFERENCES contas(id)
        );
    ");

    echo "Migração concluída com sucesso.\n";
} catch (PDOException $e) {
    echo "Erro ao criar tabelas: " . $e->getMessage() . "\n";
    exit(1);
}
