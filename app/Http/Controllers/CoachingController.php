<?php

namespace App\Http\Controllers;

use App\Models\TypeDeCoaching;
use App\Models\ReservationCoaching;
use App\Models\DisponibiliteCoaching;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\LienGoogle;

class CoachingController extends Controller
{
    public function index()
    {
        $coachingTypes = TypeDeCoaching::where('Statut', 'Publié')->get();
        $availabilities = DisponibiliteCoaching::where('EstReserve', false)
            ->where('DateDisponible', '>=', now()->toDateString())
            ->orderBy('DateDisponible')
            ->orderBy('HeureDebut')
            ->get();

        return Inertia::render('Coachings', [
            'coachingTypes' => $coachingTypes,
            'availabilities' => $availabilities
        ]);
    }

    public function storeReservation(Request $request)
    {
        $validated = $request->validate([
            'IdTypeCoaching' => 'required|exists:TypeDeCoaching,IdTypeCoaching',
            'IdDisponibilite' => 'required|exists:DisponibiliteCoaching,IdDisponibilite',
            'HeureChoisie' => 'required',
            'NoteUtilisateur' => 'nullable|string',
        ]);

        // Check if availability is still free
        $availability = DisponibiliteCoaching::findOrFail($validated['IdDisponibilite']);
        if ($availability->EstReserve) {
            return back()->withErrors(['IdDisponibilite' => 'Ce créneau vient d\'être réservé.']);
        }

        $reservation = ReservationCoaching::create([
            'IdUtilisateur' => auth()->id(),
            'IdTypeCoaching' => $validated['IdTypeCoaching'],
            'IdDisponibilite' => $validated['IdDisponibilite'],
            'HeureDebutReservation' => $validated['HeureChoisie'],
            'StatutReservation' => 'En attente',
            'NoteUtilisateur' => $validated['NoteUtilisateur'] ?? null,
        ]);

        // Mark slot as reserved
        $availability->update(['EstReserve' => true]);

        return back()->with('success', 'Votre demande de coaching a été envoyée avec succès.');
    }

    public function adminIndex()
    {
        $coachingTypes = TypeDeCoaching::withCount('reservations')->get();
        $reservations = ReservationCoaching::with(['utilisateur', 'type', 'disponibilite'])->get();
        $availabilities = DisponibiliteCoaching::orderBy('DateDisponible', 'desc')->get();
        $googleMeetLink = LienGoogle::first();
        
        return Inertia::render('PagesAdmin/AdminCoachings', [
            'coachingTypes' => $coachingTypes,
            'reservations' => $reservations,
            'availabilities' => $availabilities,
            'googleMeetLink' => $googleMeetLink ? $googleMeetLink->LienGoogleMeet : '',
            'replays' => []
        ]);
    }

    public function storeLienGoogle(Request $request)
    {
        $validated = $request->validate([
            'LienGoogleMeet' => 'required|url'
        ]);

        $lien = LienGoogle::first();
        if ($lien) {
            $lien->update($validated);
        } else {
            LienGoogle::create($validated);
        }

        return back()->with('success', 'Lien Google Meet mis à jour.');
    }

    public function storeType(Request $request)
    {
        $validated = $request->validate([
            'NomDeType' => 'required|string|max:100',
            'Descriptions' => 'nullable|string',
            'Prix' => 'nullable|numeric',
            'Statut' => 'required|string|in:Publié,Dépublié',
        ]);

        TypeDeCoaching::create($validated);

        return back()->with('success', 'Type de coaching créé avec succès.');
    }

    public function updateType(Request $request, $id)
    {
        $type = TypeDeCoaching::findOrFail($id);
        
        $validated = $request->validate([
            'NomDeType' => 'required|string|max:100',
            'Descriptions' => 'nullable|string',
            'Prix' => 'nullable|numeric',
            'Statut' => 'required|string|in:Publié,Dépublié',
        ]);

        $type->update($validated);

        return back()->with('success', 'Type de coaching mis à jour.');
    }

    public function storeAvailabilities(Request $request)
    {
        $request->validate([
            'slots' => 'required|array',
            'slots.*.DateDisponible' => 'required|date',
            'slots.*.HeureDebut' => 'required',
            'slots.*.HeureFin' => 'required',
        ]);

        // Option 1: Delete all future unreserved slots and replace them
        // This is simpler given the UI sends the full list
        DisponibiliteCoaching::where('EstReserve', false)
            ->where('DateDisponible', '>=', now()->toDateString())
            ->delete();

        foreach ($request->slots as $slot) {
            // Only insert if valid
            if ($slot['DateDisponible'] && $slot['HeureDebut'] && $slot['HeureFin']) {
                DisponibiliteCoaching::create([
                    'DateDisponible' => $slot['DateDisponible'],
                    'HeureDebut' => $slot['HeureDebut'],
                    'HeureFin' => $slot['HeureFin']
                ]);
            }
        }

        return back()->with('success', 'Disponibilités mises à jour.');
    }

    public function updateStatus(Request $request, $id)
    {
        $type = TypeDeCoaching::findOrFail($id);
        $type->Statut = $request->Statut;
        $type->save();

        return back()->with('success', 'Statut mis à jour.');
    }
}
