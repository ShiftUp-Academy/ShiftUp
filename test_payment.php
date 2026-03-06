<?php

$consumerKey    = 'SoVoRg56qw27VycfR3AogsdhpvQa';
$consumerSecret = '5Ur8Uz7gXnwdKFM3bsuEfZxxRlUa';
$merchantNumber = '0383454081';

// ── Étape 1 : Token ──────────────────────────────────────────────────────────
echo "=== ÉTAPE 1 : TOKEN ===\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://developer.mvola.mg/oauth2/token');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials&scope=EXT_INT_MVOLA_SCOPE');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Basic ' . base64_encode($consumerKey . ':' . $consumerSecret),
    'Content-Type: application/x-www-form-urlencoded',
    'Cache-Control: no-cache'
]);
$tokenResult = curl_exec($ch);
$tokenCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$tokenJson = json_decode($tokenResult, true);

if ($tokenCode !== 200 || empty($tokenJson['access_token'])) {
    echo "❌ Échec token (HTTP $tokenCode) : $tokenResult\n";
    exit(1);
}

$token = $tokenJson['access_token'];
echo "✅ Token OK (expire dans " . $tokenJson['expires_in'] . "s)\n\n";

// ── Étape 2 : Paiement Production ────────────────────────────────────────────
echo "=== ÉTAPE 2 : TRANSACTION PRODUCTION ===\n";

// Format MSISDN
$formatMsisdn = function($n) {
    $n = preg_replace('/\D/', '', $n);
    if (str_starts_with($n, '261')) return $n;
    if (str_starts_with($n, '0'))   return '261' . substr($n, 1);
    return '261' . $n;
};

$orderId         = (string)time();
$merchantMsisdn  = $formatMsisdn($merchantNumber);
$customerMsisdn  = '261347113486'; // numéro test (le vôtre ou un autre)
$correlationId   = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff),
    mt_rand(0,0x0fff)|0x4000, mt_rand(0,0x3fff)|0x8000,
    mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff)
);

$requestDate = date('Y-m-d\TH:i:s.') . substr(microtime(), 2, 3) . 'Z';

$payload = json_encode([
    "amount"                                   => "1000",
    "currency"                                 => "Ar",
    "descriptionText"                          => "Achat ShiftUp $orderId",
    "requestingOrganisationTransactionReference"=> $orderId,
    "requestDate"                              => $requestDate,
    "originalTransactionReference"             => $orderId,
    "debitParty"                               => [["key" => "msisdn", "value" => $customerMsisdn]],
    "creditParty"                              => [["key" => "msisdn", "value" => $merchantMsisdn]],
    "metadata"                                 => [["key" => "partnerName", "value" => "ShiftUp"]]
]);

echo "merchantMsisdn  : $merchantMsisdn\n";
echo "customerMsisdn  : $customerMsisdn\n";
echo "orderId         : $orderId\n";
echo "requestDate     : $requestDate\n";
echo "correlationId   : $correlationId\n";
echo "URL             : https://api.mvola.mg/mvola/mm/transactions/type/merchantpay/1.0.0/\n\n";
echo "Payload :\n$payload\n\n";

$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, 'https://api.mvola.mg/mvola/mm/transactions/type/merchantpay/1.0.0/');
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_POST, true);
curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch2, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json',
    'Accept: application/json',
    'Accept-Charset: utf-8',
    'Version: 1.0',
    'X-CorrelationID: ' . $correlationId,
    'Cache-Control: no-cache',
    'UserLanguage: FR',
    'UserAccountIdentifier: msisdn;' . $merchantMsisdn,
    'partnerName: ShiftUp',
]);
$payResult = curl_exec($ch2);
$payCode   = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
$payErr    = curl_error($ch2);
curl_close($ch2);

echo "=== RÉSULTAT ===\n";
echo "HTTP Code : $payCode\n";
if ($payErr) echo "cURL Error: $payErr\n";
echo "Response  : $payResult\n\n";

$payJson = json_decode($payResult, true);
if ($payCode === 200 || $payCode === 202) {
    echo "✅ Transaction initiée avec succès !\n";
    echo "serverCorrelationId : " . ($payJson['serverCorrelationId'] ?? '?') . "\n";
    echo "status              : " . ($payJson['status'] ?? '?') . "\n";
} elseif ($payCode === 401) {
    echo "❌ 401 - Token invalide ou scope insuffisant\n";
} elseif ($payCode === 400) {
    echo "❌ 400 - Champ manquant ou invalide\n";
    echo "errorCode        : " . ($payJson['errorCode'] ?? '?') . "\n";
    echo "errorDescription : " . ($payJson['errorDescription'] ?? '?') . "\n";
} elseif ($payCode === 403) {
    echo "❌ 403 - Accès interdit (application pas autorisée en production ?)\n";
} else {
    echo "❌ Erreur HTTP $payCode\n";
}
