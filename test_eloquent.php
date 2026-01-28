<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProgrammeFormation;

try {
    echo "Count: " . ProgrammeFormation::count() . "\n";
    $p = ProgrammeFormation::first();
    echo "First program: " . $p->Titre . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
