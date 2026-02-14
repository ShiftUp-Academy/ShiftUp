<?php

namespace App\Http\Controllers;

use App\Models\TypeDeCoaching;
use App\Models\ReservationCoaching;
use App\Models\DisponibiliteCoaching;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

use App\Models\LienGoogle;
use App\Models\Utilisateur;
use App\Notifications\NouveauContenuNotification;
use Illuminate\Support\Facades\Notification;

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

        $availability = DisponibiliteCoaching::findOrFail($validated['IdDisponibilite']);
        if ($availability->EstReserve) {
            return back()->withErrors(['IdDisponibilite' => 'Ce créneau vient d\'être réservé.']);
        }

        $originalStart = $availability->HeureDebut;
        $originalEnd = $availability->HeureFin;
        $chosenStartStr = $validated['HeureChoisie'] . ':00';
        
        $chosenStart = Carbon::createFromFormat('H:i:s', $chosenStartStr);
        $blockEnd = Carbon::createFromFormat('H:i:s', $originalEnd);
        
        // Calculer la fin du verrouillage (1h session + 3h repos = 4h total)
        $sessionEnd = $chosenStart->copy()->addHour();
        $lockEnd = $sessionEnd->copy()->addHours(3);

        // Mise à jour préventive du slot actuel pour libérer la contrainte unique sur l'heure de début originale
        // On fait de ce slot celui de la RÉSERVATION
        $availability->HeureDebut = $chosenStart->format('H:i:s');
        $availability->HeureFin = ($blockEnd->lt($lockEnd)) ? $originalEnd : $lockEnd->format('H:i:s');
        $availability->EstReserve = true;
        $availability->save();

        // 1. Créer le bloc libre AVANT la session si nécessaire
        if ($chosenStart->format('H:i:s') > $originalStart) {
            DisponibiliteCoaching::create([
                'DateDisponible' => $availability->DateDisponible,
                'HeureDebut' => $originalStart,
                'HeureFin' => $chosenStart->format('H:i:s'),
                'EstReserve' => false
            ]);
        }

        // 2. Créer le bloc libre APRÈS le verrouillage si nécessaire
        if ($blockEnd->gt($lockEnd)) {
            DisponibiliteCoaching::create([
                'DateDisponible' => $availability->DateDisponible,
                'HeureDebut' => $lockEnd->format('H:i:s'),
                'HeureFin' => $originalEnd,
                'EstReserve' => false
            ]);
        }

        $reservation = ReservationCoaching::create([
            'IdUtilisateur' => auth()->id(),
            'IdTypeCoaching' => $validated['IdTypeCoaching'],
            'IdDisponibilite' => $availability->IdDisponibilite,
            'HeureDebutReservation' => $validated['HeureChoisie'],
            'StatutReservation' => 'En attente',
            'NoteUtilisateur' => $validated['NoteUtilisateur'] ?? null,
        ]);

        // Verrouiller les autres micro-créneaux éventuels dans la zone de repos
        DisponibiliteCoaching::where('DateDisponible', $availability->DateDisponible)
            ->where('HeureDebut', '>=', $chosenStart->format('H:i:s'))
            ->where('HeureDebut', '<', $lockEnd->format('H:i:s'))
            ->where('IdDisponibilite', '!=', $availability->IdDisponibilite)
            ->where('EstReserve', false)
            ->update([
                'EstReserve' => true,
                'BlockedByReservationId' => $reservation->IdReservation
            ]);

        // Notifications
        $reservation->load(['utilisateur.profil', 'type', 'disponibilite']);
        
        // Au client
        auth()->user()->notify(new \App\Notifications\CoachingReservationNotification($reservation, 'client'));
        
        // À l'admin
        $admin = Utilisateur::where('Role', 'admin')->first();
        if ($admin) {
            $admin->notify(new \App\Notifications\CoachingReservationNotification($reservation, 'admin'));
        }

        return back()->with('success', 'Votre demande de coaching a été envoyée avec succès.');
    }

    public function adminIndex()
    {
        $today = now()->toDateString();
        
        ReservationCoaching::whereHas('disponibilite', function($q) use ($today) {
            $q->where('DateDisponible', '<', $today);
        })->whereIn('StatutReservation', ['Confirmé', 'En attente'])
          ->update(['StatutReservation' => 'Terminé']);


        $coachingTypes = TypeDeCoaching::withCount('reservations')->get();
        
        $reservations = ReservationCoaching::with(['utilisateur.profil', 'type', 'disponibilite'])
            ->join('DisponibiliteCoaching', 'ReservationCoaching.IdDisponibilite', '=', 'DisponibiliteCoaching.IdDisponibilite')
            ->orderBy('DisponibiliteCoaching.DateDisponible', 'asc') // Le plus proche d'abord
            ->orderBy('DisponibiliteCoaching.HeureDebut', 'asc')
            ->select('ReservationCoaching.*') // Important pour éviter les conflits d'ID
            ->get();

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

        $type = TypeDeCoaching::create($validated);

        // Notification aux utilisateurs
        if ($type->Statut === 'Publié') {
            $users = Utilisateur::where('Role', '!=', 'admin')->get();
            Notification::send($users, new NouveauContenuNotification($type, 'Coaching'));
        }

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

        $wasPublished = $type->Statut === 'Publié';
        $type->update($validated);

        if ($type->Statut === 'Publié' && !$wasPublished) {
            $users = Utilisateur::where('Role', '!=', 'admin')->get();
            Notification::send($users, new NouveauContenuNotification($type, 'Coaching'));
        }

        return back()->with('success', 'Type de coaching mis à jour.');
    }

    public function storeAvailabilities(Request $request)
    {
        $request->validate([
            'slots' => 'required|array',
            'slots.*.IdDisponibilite' => 'nullable',
            'slots.*.DateDisponible' => 'required|date',
            'slots.*.HeureDebut' => 'required',
            'slots.*.HeureFin' => 'required',
        ]);

        foreach ($request->slots as $slot) {
            if (!empty($slot['DateDisponible']) && !empty($slot['HeureDebut']) && !empty($slot['HeureFin'])) {
                $id = $slot['IdDisponibilite'] ?? null;
                
                if ($id) {
                    $avail = DisponibiliteCoaching::find($id);
                    if ($avail) {
                        $avail->DateDisponible = $slot['DateDisponible'];
                        $avail->HeureDebut = $slot['HeureDebut'];
                        $avail->HeureFin = $slot['HeureFin'];
                        $avail->save();
                    }
                } else {
                    DisponibiliteCoaching::create([
                        'DateDisponible' => $slot['DateDisponible'],
                        'HeureDebut' => $slot['HeureDebut'],
                        'HeureFin' => $slot['HeureFin']
                    ]);
                }
            }
        }

        return back()->with('success', 'Disponibilités mises à jour.');
    }

    public function destroyAvailability($id)
    {
        $avail = DisponibiliteCoaching::findOrFail($id);
        
        // On ne bloque la suppression que si le créneau est réservé ET qu'il est dans le futur
        if ($avail->EstReserve && $avail->DateDisponible >= now()->toDateString()) {
            return back()->withErrors(['error' => 'Impossible de supprimer un créneau réservé pour une date future ou actuelle.']);
        }
        
        $avail->delete();

        return back()->with('success', 'Créneau de disponibilité supprimé avec succès.');
    }

    public function updateStatus(Request $request, $id)
    {
        $type = TypeDeCoaching::findOrFail($id);
        $wasPublished = $type->Statut === 'Publié';
        $type->Statut = $request->Statut;
        $type->save();

        if ($type->Statut === 'Publié' && !$wasPublished) {
            $users = Utilisateur::where('Role', '!=', 'admin')->get();
            Notification::send($users, new NouveauContenuNotification($type, 'Coaching'));
        }

        return back()->with('success', 'Statut mis à jour.');
    }
    public function updateReservation(Request $request, $id)
    {
        $reservation = ReservationCoaching::findOrFail($id);
        
        $validated = $request->validate([
            'StatutReservation' => 'required|string|in:En attente,Confirmé,Terminé,Annulé,Remboursé',
            'LienVideoReplay' => 'nullable|string',
            'StatutReplay' => 'nullable|string|in:Publié,Dépublié',
        ]);

        $reservation->update($validated);

        if ($validated['StatutReservation'] === 'Annulé') {
            $reservation->disponibilite()->update(['EstReserve' => false]);
            
            DisponibiliteCoaching::where('BlockedByReservationId', $reservation->IdReservation)
                ->update([
                    'EstReserve' => false,
                    'BlockedByReservationId' => null
                ]);
        }

        return back()->with('success', 'Réservation mise à jour avec succès.');
    }
}
