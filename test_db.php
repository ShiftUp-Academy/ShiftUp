<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Offre;
use App\Models\ProgrammeFormation;
use App\Models\TypeDeCoaching;

try {
    echo "Checking Offre count: " . Offre::count() . "\n";
    echo "Checking ProgrammeFormation count: " . ProgrammeFormation::count() . "\n";
    echo "Checking TypeDeCoaching count: " . TypeDeCoaching::count() . "\n";
    echo "All models are accessible!\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
