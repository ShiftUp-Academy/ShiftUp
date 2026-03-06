<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ProgrammeFormation;

$p = ProgrammeFormation::first();
if ($p) {
    echo "ID: " . $p->IdProgrammeFormation . "\n";
    echo "FR: " . $p->getTranslation('Titre', 'fr') . "\n";
    echo "EN: " . $p->getTranslation('Titre', 'en') . "\n";
    echo "MG: " . $p->getTranslation('Titre', 'mg') . "\n";
} else {
    echo "No data found\n";
}
