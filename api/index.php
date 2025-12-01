<?php
// usdcfaucet.hedera.co.ke/api/index.php

// Basic CORS (adjust origins if you want to lock this down)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'ok'    => false,
        'error' => 'Only POST is allowed',
    ]);
    exit;
}

// Read and decode JSON body
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode([
        'ok'    => false,
        'error' => 'Invalid JSON body',
    ]);
    exit;
}

$address = trim($data['address'] ?? '');
$amount  = isset($data['amount']) ? (int)$data['amount'] : 0;

// Basic validation
if ($address === '' || $amount <= 0) {
    http_response_code(400);
    echo json_encode([
        'ok'    => false,
        'error' => 'address and positive amount are required',
    ]);
    exit;
}

// Faucet settings
$TOKEN_ID          = '0.0.7352375';        // tUSDC token ID
$DECIMALS          = 6;                    // tUSDC decimals
$MAX_PER_REQUEST   = 1000;                 // maximum per single call (whole tUSDC)
$MAX_PER_DAY       = 1000;                 // maximum per day per address (whole tUSDC)
$NODE_BASE_URL     = 'http://127.0.0.1:5050'; // your local Hedera API
$HASHSCAN_BASE_URL = 'https://hashscan.io/testnet/transaction/';

// Enforce per-request limit
if ($amount > $MAX_PER_REQUEST) {
    $amount = $MAX_PER_REQUEST;
}

// Simple per-address per-day rate limiting using a JSON file
$logFile = __DIR__ . '/faucet_log.json';
$today   = date('Y-m-d');
$log     = [];

if (file_exists($logFile)) {
    $decoded = json_decode(file_get_contents($logFile), true);
    if (is_array($decoded)) {
        $log = $decoded;
    }
}

if (!isset($log[$address])) {
    $log[$address] = [
        'date'  => $today,
        'total' => 0,
    ];
}

if ($log[$address]['date'] !== $today) {
    // New day, reset counter
    $log[$address]['date']  = $today;
    $log[$address]['total'] = 0;
}

// Check daily cap
if ($log[$address]['total'] + $amount > $MAX_PER_DAY) {
    http_response_code(429);
    echo json_encode([
        'ok'    => false,
        'error' => 'Daily faucet limit reached for this address (max ' . $MAX_PER_DAY . ' tUSDC per day).',
    ]);
    exit;
}

// Convert to base units (respecting 6 decimals)
// Example: amount=1000 => baseUnits = 1000 * 10^6
$baseUnits = $amount * (10 ** $DECIMALS);

// Small helper to call your Node API
function callNodeApi(string $url, array $payload): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_TIMEOUT        => 20,
    ]);
    $resp  = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['ok' => false, 'error' => 'cURL error: ' . $error];
    }

    $json = json_decode($resp, true);
    if (!is_array($json)) {
        return ['ok' => false, 'error' => 'Invalid JSON from node API: ' . $resp];
    }

    return $json;
}

// 1) Mint tUSDC into treasury (in base units)
$mintPayload = [
    'tokenId' => $TOKEN_ID,
    'amount'  => $baseUnits, // fungible amount in smallest units
    // 'supplyKey' => '...' // optional, if not in DB; your Node API can load from DB
];

$mintResp = callNodeApi($NODE_BASE_URL . '/tokens/mint', $mintPayload);

if (empty($mintResp['ok'])) {
    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'stage' => 'mint',
        'error' => $mintResp['error'] ?? 'Mint failed',
        'raw'   => $mintResp,
    ]);
    exit;
}

// 2) Transfer tUSDC from treasury (operator) to the recipient address
// NOTE: /tokens/transfer expects Hedera account ID (0.0.x). If you want
// to fully support 0x EVM addresses, adjust the Node service to handle that.
$transferPayload = [
    'tokenId'     => $TOKEN_ID,
    'toAccountId' => $address,
    'amount'      => $baseUnits,
    'memo'        => 'tUSDC faucet',
];

$transferResp = callNodeApi($NODE_BASE_URL . '/tokens/transfer', $transferPayload);

if (empty($transferResp['ok'])) {
    http_response_code(500);
    echo json_encode([
        'ok'        => false,
        'stage'     => 'transfer',
        'error'     => $transferResp['error'] ?? 'Transfer failed',
        'mintResp'  => $mintResp,
        'raw'       => $transferResp,
    ]);
    exit;
}

// At this point the request succeeded, update the daily log
$log[$address]['total'] += $amount;
file_put_contents($logFile, json_encode($log, JSON_PRETTY_PRINT));

// Build explorer URL from transfer txId if present
$txId        = $transferResp['txId'] ?? null;
$explorerUrl = $txId ? $HASHSCAN_BASE_URL . urlencode($txId) : null;

// Final response to the frontend
echo json_encode([
    'ok'              => true,
    'address'         => $address,
    'amount'          => $amount,
    'amountBaseUnits' => $baseUnits,
    'mintTxId'        => $mintResp['txId']   ?? null,
    'mintStatus'      => $mintResp['status'] ?? null,
    'txId'            => $txId,
    'status'          => $transferResp['status'] ?? null,
    'explorerUrl'     => $explorerUrl,
]);
