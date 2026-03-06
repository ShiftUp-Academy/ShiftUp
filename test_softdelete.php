<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Offre;

$o = Offre::first();
if ($o) {
    echo "Found: " . $o->Titre . "\n";
    $o->delete();
    echo "Deleted via SoftDelete\n";
    $trashedCount = Offre::onlyTrashed()->count();
    echo "Trashed count: " . $trashedCount . "\n";
    $o->restore();
    echo "Restored\n";
} else {
    echo "No Offre found\n";
}
