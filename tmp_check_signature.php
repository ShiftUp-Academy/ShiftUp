<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->boot();

use App\Models\ProgrammeFormation;

try {
    $p = ProgrammeFormation::with(['auteur'])
        ->where(function($query) {
            $query->where('Type', '!=', 'Seminaire');
        })->get();
    echo "SUCCESS query on Eloquent\Builder\n";
} catch (Throwable $e) {
    echo "ERROR Eloquent\Builder: " . $e->getMessage() . "\n";
}

try {
    $c = collect([['foo' => 'bar']])->where(function($q) { return true; });
    echo "SUCCESS query on Collection\n";
} catch (Throwable $e) {
    echo "ERROR Collection: " . $e->getMessage() . "\n";
}
