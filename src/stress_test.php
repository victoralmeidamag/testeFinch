<?php

$start = microtime(true);
$results = [];

$requests = 50;
$url = 'http://nginx/transfer';

$logDir = __DIR__ . '/logs';
$logFile = $logDir . '/stress_deadlock.log';

if (!file_exists($logDir)) {
    mkdir($logDir, 0777, true);
}

file_put_contents($logFile, "Stress Test (Deadlock Simulation) - " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

$children = [];

for ($i = 0; $i < $requests; $i++) {
    $pid = pcntl_fork();

    if ($pid === -1) {
        die("Erro ao criar processo filho\n");
    }

    if ($pid === 0) {
        $from = $i % 2 === 0 ? 1 : 2;
        $to = $i % 2 === 0 ? 2 : 1;

        $payload = [
            'from' => $from,
            'to' => $to,
            'amount' => 1.00
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $log = "[$i] HTTP $httpCode - From: $from To: $to - ";
        if ($httpCode !== 200 || $error) {
            $log .= "Erro: $error - Resposta: $response\n";
        } else {
            $log .= "Sucesso - Resposta: $response\n";
        }

        file_put_contents($logFile, $log, FILE_APPEND);
        exit(0);
    }

    $children[] = $pid;
}

foreach ($children as $pid) {
    pcntl_waitpid($pid, $status);
}

$end = microtime(true);
$totalTime = round($end - $start, 2);

file_put_contents($logFile, "Finalizado em {$totalTime} segundos.\n\n", FILE_APPEND);
echo json_encode([
    'message' => 'Stress test com simulação de deadlock concluído',
    'total_requests' => $requests,
    'total_time_sec' => $totalTime
], JSON_PRETTY_PRINT);