<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $tables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
    echo "Tables in database:\n";
    foreach ($tables as $table) {
        echo "- " . $table->tablename . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
