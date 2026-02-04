<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Offre;

try {
    echo "Attempting to fetch offres with relations...\n";
    $offres = Offre::with(['programmes.programme', 'coachings.coaching'])
        ->orderBy('DateCreation', 'desc')
        ->get();
    echo "Found " . $offres->count() . " offres.\n";
    echo "Success!\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
