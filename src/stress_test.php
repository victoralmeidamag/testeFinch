<?php

$start = microtime(true);
$results = [];

$requests = 50;
$url = 'http://nginx/transfer';
$payload = [
    'from' => 1,
    'to' => 2,
    'amount' => 1.00
];

$logDir = __DIR__ . '/logs';
$logFile = $logDir . '/stress_errors.log';

if (!file_exists($logDir)) {
    mkdir($logDir, 0777, true);
}

file_put_contents($logFile, "Stress Test - " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

for ($i = 0; $i < $requests; $i++) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 5,
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        file_put_contents($logFile, "[$i] HTTP $httpCode - Erro: $error - Resposta: $response\n", FILE_APPEND);
    }

    $results[] = [
        'success' => $httpCode === 200,
        'http_code' => $httpCode,
        'error' => $error,
        'response' => $response
    ];
}

$end = microtime(true);
$totalTime = round($end - $start, 2);

$successCount = count(array_filter($results, fn($r) => $r['success']));
$failCount = $requests - $successCount;

$summary = [
    'total_requests' => $requests,
    'success' => $successCount,
    'failures' => $failCount,
    'total_time_sec' => $totalTime
];

file_put_contents($logFile, "Resumo: " . json_encode($summary) . "\n\n", FILE_APPEND);

echo json_encode($summary, JSON_PRETTY_PRINT);
