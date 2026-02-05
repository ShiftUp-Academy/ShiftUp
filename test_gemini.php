<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Gemini\Laravel\Facades\Gemini;

try {
    echo "Testing Gemini API...\n";
    $result = Gemini::generativeModel('gemini-1.5-pro')->generateContent('Dis bonjour en une phrase');
    echo "Success! Response:\n";
    echo $result->text();
    echo "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
