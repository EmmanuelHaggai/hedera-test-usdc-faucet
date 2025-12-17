<?php
// usdcfaucet.hedera.co.ke/api/resolve-account.php

// Basic CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

header('Content-Type: application/json');

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'ok'    => false,
        'error' => 'Only POST is allowed',
    ]);
    exit;
}

// Read client body
$raw = file_get_contents('php://input');
$body = json_decode($raw, true);

if (!is_array($body)) {
    http_response_code(400);
    echo json_encode([
        'ok'    => false,
        'error' => 'Invalid JSON body',
    ]);
    exit;
}

// Forward to local Node API
$NODE_BASE_URL = 'http://127.0.0.1:5050';

$ch = curl_init($NODE_BASE_URL . '/resolve-account');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS     => json_encode($body),
    CURLOPT_TIMEOUT        => 10,
]);

$resp  = curl_exec($ch);
$error = curl_error($ch);
$code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($error) {
    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'error' => 'Proxy cURL error: ' . $error,
    ]);
    exit;
}

// Try to pass through Node’s status code if reasonable
if ($code >= 400) {
    http_response_code($code);
}

// Pass back Node’s JSON directly
echo $resp;
