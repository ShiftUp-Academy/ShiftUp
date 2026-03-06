<?php

$consumerKey    = 'SoVoRg56qw27VycfR3AogsdhpvQa';
$consumerSecret = '5Ur8Uz7gXnwdKFM3bsuEfZxxRlUa';

echo "=== TEST TOKEN MVola Production ===\n";
echo "Consumer Key    : $consumerKey\n";
echo "Consumer Secret : $consumerSecret\n";
echo "Encoded Basic   : " . base64_encode($consumerKey . ':' . $consumerSecret) . "\n\n";

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

$result   = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

echo "HTTP Code : $httpCode\n";
if ($curlErr) {
    echo "cURL Error : $curlErr\n";
}
echo "Response  : $result\n\n";

$json = json_decode($result, true);
if (isset($json['access_token'])) {
    echo "✅ TOKEN OBTENU avec succès !\n";
    echo "Token     : " . substr($json['access_token'], 0, 40) . "...\n";
    echo "Expires   : " . ($json['expires_in'] ?? '?') . " secondes\n";
} elseif ($httpCode === 401) {
    echo "❌ 401 - Credentials invalides (clé/secret incorrects ou app non activée en production)\n";
} elseif ($httpCode === 400) {
    echo "❌ 400 - Mauvaise requête : " . ($json['error_description'] ?? $result) . "\n";
} else {
    echo "❌ Erreur inconnue (HTTP $httpCode)\n";
}
