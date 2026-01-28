<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Querying with quotes and PascalCase:\n";
    $res = DB::select('SELECT 1 FROM "ProgrammeFormation" LIMIT 1');
    echo "Success!\n";
} catch (\Exception $e) {
    echo "Failed: PascalCase quoted: " . $e->getMessage() . "\n";
}

try {
    echo "Querying with quotes and lowercase:\n";
    $res = DB::select('SELECT 1 FROM "programmeformation" LIMIT 1');
    echo "Success!\n";
} catch (\Exception $e) {
    echo "Failed: lowercase quoted: " . $e->getMessage() . "\n";
}

try {
    echo "Querying UNQUOTED PascalCase:\n";
    $res = DB::select('SELECT 1 FROM ProgrammeFormation LIMIT 1');
    echo "Success!\n";
} catch (\Exception $e) {
    echo "Failed: PascalCase unquoted: " . $e->getMessage() . "\n";
}
