<?php

// Read API key from .env file
$envContent = file_get_contents(__DIR__ . '/.env');
preg_match('/GEMINI_API_KEY=(.+)/', $envContent, $matches);
$apiKey = trim($matches[1]);

echo "Testing with API key (first 10 chars): " . substr($apiKey, 0, 10) . "...\n\n";

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent?key={$apiKey}";

$data = [
    'contents' => [
        [
            'parts' => [
                ['text' => 'Dis bonjour en une phrase']
            ]
        ]
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: {$httpCode}\n";
if ($error) {
    echo "CURL Error: {$error}\n";
}
echo "Response: {$response}\n";
