<?php
// // Create a fungible token for Hedera testnet "USDC" style token:
// $payload = json_encode([
//   "name"         => "Hedera Testnet USDC",
//   "symbol"       => "tUSDC",
//   "decimals"     => 6,                 // USDC-style precision
//   "initialSupply"=> 0,                 // start at 0, faucet will mint
//   "supplyType"   => "INFINITE",          // or "INFINITE" if your service supports it
//   // "treasuryAccountId" => "0.0.xxxxx", // optional: set if your service requires it
// ]);

// $ch = curl_init("http://127.0.0.1:5050/tokens");
// curl_setopt_array($ch, [
//   CURLOPT_RETURNTRANSFER => true,
//   CURLOPT_POST           => true,
//   CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
//   CURLOPT_POSTFIELDS     => $payload,
// ]);
// $resp = curl_exec($ch);
// curl_close($ch);

// echo $resp;

// {"ok":true,"tokenId":"0.0.7352375","status":"SUCCESS","supplyKey":"5764d4eb7269fc0ec2d030ec3e08ffe787c46ec51cad263a57a0f8d534bf5a91"}



// This tests ONLY the Node endpoint:
//    http://127.0.0.1:5050/resolve-account
// and does NOT go through your PHP proxy.

$nodeUrl = "http://127.0.0.1:5050/resolve-account";

// Hedera account to test
$accountId = "0.0.7054893";

$payload = json_encode([
    "accountId" => $accountId
]);

$ch = curl_init($nodeUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ["Content-Type: application/json"],
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_TIMEOUT        => 10
]);

$response = curl_exec($ch);
$error    = curl_error($ch);
$code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $code\n\n";

if ($error) {
    echo "cURL ERROR: $error\n";
    exit;
}

echo "Raw Response:\n";
echo $response . "\n\n";

// Decode JSON for clarity
$data = json_decode($response, true);
echo "Decoded JSON:\n";
print_r($data);

