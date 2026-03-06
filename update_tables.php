<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

try {
    if (!Schema::hasColumn('Consultations', 'deleted_at')) {
        Schema::table('Consultations', function (Blueprint $table) { $table->softDeletes(); });
        echo "Consultations: Updated\n";
    }
    if (!Schema::hasColumn('Offres', 'deleted_at')) {
        Schema::table('Offres', function (Blueprint $table) { $table->softDeletes(); });
        echo "Offres: Updated\n";
    }
    if (!Schema::hasColumn('Temoignage', 'deleted_at')) {
        Schema::table('Temoignage', function (Blueprint $table) { $table->softDeletes(); });
        echo "Temoignage: Updated\n";
    }
    if (!Schema::hasColumn('TypeDeCoaching', 'deleted_at')) {
        Schema::table('TypeDeCoaching', function (Blueprint $table) { $table->softDeletes(); });
        echo "TypeDeCoaching: Updated\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
