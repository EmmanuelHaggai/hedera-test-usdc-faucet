<?php
// usdcfaucet.hedera.co.ke/api/index.php

// Force UTC to keep timestamps consistent
date_default_timezone_set('UTC');

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
$raw  = file_get_contents('php://input');
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
$TOKEN_ID             = '0.0.7352375';          // tUSDC token ID
$DECIMALS             = 6;                      // tUSDC decimals
$MAX_PER_REQUEST      = 1000;                   // maximum per single call (whole tUSDC)
$MAX_PER_DAY          = 1000;                   // maximum per rolling 24h window per address
$WINDOW_SECONDS       = 24 * 60 * 60;           // 24-hour window
$NODE_BASE_URL        = 'http://127.0.0.1:5050';
$HASHSCAN_BASE_URL    = 'https://hashscan.io/testnet/transaction/';
$LOG_JSON_FILE        = __DIR__ . '/faucet_log.json';
$FUNDED_ADDRESSES_TXT = __DIR__ . '/faucet_funded_addresses.txt';

// Enforce per-request limit (server-side)
if ($amount > $MAX_PER_REQUEST) {
    http_response_code(400);
    echo json_encode([
        'ok'              => false,
        'error'           => 'Maximum per request is ' . $MAX_PER_REQUEST . ' tUSDC.',
        'requestedAmount' => $amount,
        'maxPerRequest'   => $MAX_PER_REQUEST,
    ]);
    exit;
}

// Rolling 24h window per address using a JSON file
$now        = time();
$log        = [];
$addressKey = strtolower($address); // normalize as key

if (file_exists($LOG_JSON_FILE)) {
    $contents = file_get_contents($LOG_JSON_FILE);
    $decoded  = json_decode($contents, true);
    if (is_array($decoded)) {
        $log = $decoded;
    } else {
        $log = [];
    }
}

// Structure:
//
// $log[$addressKey] = [
//     'windowStart' => <unix_timestamp>,  // start of current 24h window
//     'windowTotal' => <int>,            // total tUSDC sent in this window
// ];

$windowStart = $now;
$windowTotal = 0;

if (isset($log[$addressKey])) {
    $entry       = $log[$addressKey];
    $windowStart = isset($entry['windowStart']) ? (int)$entry['windowStart'] : $now;
    $windowTotal = isset($entry['windowTotal']) ? (int)$entry['windowTotal'] : 0;

    // If current window is older than 24h, reset it
    if (($now - $windowStart) >= $WINDOW_SECONDS) {
        $windowStart = $now;
        $windowTotal = 0;
    }
}

// Now enforce the rolling 24h quota: windowTotal + amount <= MAX_PER_DAY
if ($windowTotal + $amount > $MAX_PER_DAY) {
    $nextAllowed   = $windowStart + $WINDOW_SECONDS;
    $remainingSecs = max(0, $nextAllowed - $now);
    $remaining     = max(0, $MAX_PER_DAY - $windowTotal);

    http_response_code(429);
    echo json_encode([
        'ok'    => false,
        'error' => 'This request would exceed your 24-hour faucet limit. '
            . 'You have already received ' . $windowTotal . ' tUSDC in this window and can receive up to '
            . $MAX_PER_DAY . ' tUSDC in any 24-hour window. You can still request up to '
            . $remaining . ' tUSDC right now.',
        'requestedAmount'          => $amount,
        'alreadyReceived'          => $windowTotal,
        'maxPerWindow'             => $MAX_PER_DAY,
        'remainingInCurrentWindow' => $remaining,
        'nextWindowStartIso'       => gmdate('c', $nextAllowed),
        'nextWindowStartSeconds'   => $remainingSecs,
    ]);
    exit;
}

// Convert to base units (respecting 6 decimals)
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
    'amount'  => $baseUnits,
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

// At this point the request succeeded: update rolling 24h window
$windowTotal += $amount;

$log[$addressKey] = [
    'windowStart' => $windowStart,
    'windowTotal' => $windowTotal,
];

file_put_contents($LOG_JSON_FILE, json_encode($log, JSON_PRETTY_PRINT));

// Before appending, prune text log to keep only the last 7 days
$maxDays = 7;
$cutoff  = $now - ($maxDays * 24 * 60 * 60);

if (file_exists($FUNDED_ADDRESSES_TXT)) {
    $lines    = file($FUNDED_ADDRESSES_TXT, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $filtered = [];

    foreach ($lines as $ln) {
        // Expected format: "2025-12-09T12:44:00Z | address | amount=... | txId=..."
        $parts = explode('|', $ln);
        if (count($parts) >= 1) {
            $timestampStr = trim($parts[0]);
            $timestamp    = strtotime($timestampStr);
            if ($timestamp !== false && $timestamp >= $cutoff) {
                $filtered[] = $ln;
            }
        }
    }

    file_put_contents(
        $FUNDED_ADDRESSES_TXT,
        implode("\n", $filtered) . (count($filtered) ? "\n" : "")
    );
}

// Also append a line to a human-readable text log
$txId = $transferResp['txId'] ?? '';
$line = sprintf(
    "%s | %s | amount=%d | txId=%s\n",
    gmdate('c', $now), // ISO8601 UTC
    $address,
    $amount,
    $txId
);
file_put_contents($FUNDED_ADDRESSES_TXT, $line, FILE_APPEND | LOCK_EX);

// Build explorer URL from transfer txId if present
$explorerUrl = $txId ? $HASHSCAN_BASE_URL . urlencode($txId) : null;

// Final response to the frontend
echo json_encode([
    'ok'                        => true,
    'address'                   => $address,
    'amount'                    => $amount,
    'amountBaseUnits'           => $baseUnits,
    'mintTxId'                  => $mintResp['txId']   ?? null,
    'mintStatus'                => $mintResp['status'] ?? null,
    'txId'                      => $txId,
    'status'                    => $transferResp['status'] ?? null,
    'explorerUrl'               => $explorerUrl,
    'windowStartIso'            => gmdate('c', $windowStart),
    'windowTotal'               => $windowTotal,
    'maxPerWindow'              => $MAX_PER_DAY,
    'windowResetsAtIso'         => gmdate('c', $windowStart + $WINDOW_SECONDS),
]);
