<?php

namespace App\Http\Controllers;

use App\Models\ReservationCoaching;
use App\Models\Live;
use App\Models\Commande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ReservationController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Coachings
        $coachings = ReservationCoaching::where('IdUtilisateur', $user->IdUtilisateur)
            ->with(['type', 'disponibilite'])
            ->orderBy('HeureDebutReservation', 'asc')
            ->get();

        // 2. Séminaires (purchased from commands)
        $seminairesPurchased = Commande::where('IdUtilisateur', $user->IdUtilisateur)
            ->where('Statut', 'Payé')
            ->with(['items.programme' => function($query) {
                $query->where('Type', 'Seminaire');
            }])
            ->get()
            ->flatMap(fn($c) => $c->items)
            ->filter(fn($i) => $i->programme)
            ->map(fn($i) => $i->programme)
            ->values();

        // 3. Lives (Upcoming)
        $lives = Live::where('Statut', 'Publié')
            ->where('DateFin', '>=', now())
            ->with('categorie')
            ->orderBy('DateDebut', 'asc')
            ->get();

        return Inertia::render('Reservations', [
            'coachings' => $coachings,
            'seminaires' => $seminairesPurchased,
            'lives' => $lives
        ]);
    }
}
