<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ReservationCoaching;
use Illuminate\Support\Facades\Auth;

// Get all reservations
$all = ReservationCoaching::select('IdReservation','IdUtilisateur','HeureDebutReservation','StatutReservation')
    ->orderBy('IdReservation','desc')
    ->take(5)
    ->get();

echo "=== All Reservations (last 5) ===\n";
foreach ($all as $r) {
    echo "ID: {$r->IdReservation} | User: {$r->IdUtilisateur} | Date: {$r->HeureDebutReservation} | Status: {$r->StatutReservation}\n";
}

echo "\n=== Now: " . date('Y-m-d H:i:s') . " ===\n";

// Check what the controller returns for the authenticated user
$users = \App\Models\Utilisateur::pluck('IdUtilisateur');
echo "\nAll user IDs with reservations: ";
$userIds = ReservationCoaching::distinct()->pluck('IdUtilisateur');
echo implode(', ', $userIds->toArray()) . "\n";
