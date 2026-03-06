<?php
require 'vendor/autoload.php';

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

// Simuler l'environnement Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing MVola API Direct...\n";

$consumerKey = env('MVOLA_CONSUMER_KEY');
$consumerSecret = env('MVOLA_CONSUMER_SECRET');
$merchantNumber = env('MVOLA_MERCHANT_NUMBER');

echo "1. Getting Token...\n";
$authResponse = Http::asForm()
    ->withBasicAuth($consumerKey, $consumerSecret)
    ->post('https://developer.mvola.mg/oauth2/token', [
        'grant_type' => 'client_credentials',
        'scope' => 'EXT_INT_MVOLA_SCOPE'
    ]);

if (!$authResponse->successful()) {
    die("Auth Failed: " . $authResponse->body() . "\n");
}

$token = $authResponse->json()['access_token'];
echo "Token Acquired!\n";

echo "2. Exhaustive Permutation Test...\n";
$orderId = (string)time();
$customerPhone = "0347113486";

$commonHeaders = [
    'Version' => '1.0',
    'UserLanguage' => 'FR',
    'UserAccountIdentifier' => 'msisdn;' . $merchantNumber,
    'partnerName' => 'ShiftUp',
    'Cache-Control' => 'no-cache',
    'Accept' => 'application/json',
    'Content-Type' => 'application/json'
];

$testCases = [
    "Test 1: Full Doc (amount string, ref, date, parties, metadata)" => [
        "amount" => "1000",
        "currency" => "Ar",
        "description" => "Achat",
        "transactionReference" => $orderId,
        "transactionDate" => date('Y-m-d\TH:i:s.v\Z'),
        "debitParty" => [["key" => "msisdn", "value" => $customerPhone]],
        "creditParty" => [["key" => "msisdn", "value" => $merchantNumber]],
        "metadata" => [["key" => "partnerName", "value" => "ShiftUp"]]
    ],
    "Test 2: With originalReference" => [
        "amount" => "1000",
        "currency" => "Ar",
        "description" => "Achat",
        "transactionReference" => $orderId,
        "originalReference" => $orderId,
        "transactionDate" => date('Y-m-d\TH:i:s.v\Z'),
        "debitParty" => [["key" => "msisdn", "value" => $customerPhone]],
        "creditParty" => [["key" => "msisdn", "value" => $merchantNumber]],
    ],
    "Test 3: Double Metadata" => [
        "amount" => "1000",
        "currency" => "Ar",
        "description" => "Achat",
        "transactionReference" => $orderId,
        "transactionDate" => date('Y-m-d\TH:i:s.v\Z'),
        "debitParty" => [["key" => "msisdn", "value" => $customerPhone]],
        "creditParty" => [["key" => "msisdn", "value" => $merchantNumber]],
        "metadata" => [
            ["key" => "partnerName", "value" => "ShiftUp"],
            ["key" => "fc", "value" => "001"]
        ]
    ]
];

foreach ($testCases as $name => $payload) {
    echo "\n--- $name ---\n";
    $response = Http::withToken($token)
        ->withHeaders(array_merge($commonHeaders, ['X-CorrelationID' => Str::uuid()->toString()]))
        ->post('https://devapi.mvola.mg/mvola/mm/transactions/type/merchantpay/1.0.0/', $payload);
    
    echo "Status: " . $response->status() . "\n";
    echo "Body: " . $response->body() . "\n";
}
