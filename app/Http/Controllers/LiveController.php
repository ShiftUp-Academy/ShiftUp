<?php

namespace App\Http\Controllers;

use App\Models\Live;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\Categorie;
use Illuminate\Support\Facades\Auth;
use App\Models\Utilisateur;
use App\Notifications\NouveauContenuNotification;
use Illuminate\Support\Facades\Notification;

class LiveController extends Controller
{
    // --- PARTIE PUBLIQUE ---
    public function index()
    {
        $live = Live::where('Statut', 'Publié')
            ->orderByRaw('CASE WHEN "DateFin" >= NOW() THEN 0 ELSE 1 END')
            ->orderBy('DateDebut', 'asc')
            ->first();

        return Inertia::render('LiveDetail', [
            'live' => $live
        ]);
    }

    public function show($id)
    {
        $live = Live::where('Statut', 'Publié')->findOrFail($id);

        return Inertia::render('LiveDetail', [
            'live' => $live
        ]);
    }

    // --- PARTIE ADMIN ---
    public function adminIndex()
    {
        $lives = Live::with(['categorie', 'auteur'])
            ->orderBy('DateDebut', 'asc')
            ->get();

        $categories = Categorie::all();

        return Inertia::render('PagesAdmin/AdminLives', [
            'lives' => $lives,
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Titre' => 'required|string|max:155',
            'IdCategorie' => 'nullable|exists:Categories,IdCategorie',
            'Descriptions' => 'nullable|string',
            'DateDebut' => 'required|date',
            'DateFin' => 'required|date|after:DateDebut',
            'LienGoogleMeet' => 'nullable|string',
            'LienPhoto' => 'nullable|string',
            'LienReplay' => 'nullable|string',
        ]);

        $validated['IdAuteur'] = Auth::id();
        $validated['Statut'] = 'Publié';

        $live = Live::create($validated);

        // Notification aux utilisateurs
        $users = Utilisateur::where('Role', '!=', 'admin')->get();
        Notification::send($users, new NouveauContenuNotification($live, 'Live'));

        return redirect()->back()->with('success', 'Live programmé avec succès.');
    }

    public function update(Request $request, $id)
    {
        $live = Live::findOrFail($id);

        $validated = $request->validate([
            'Titre' => 'required|string|max:155',
            'IdCategorie' => 'nullable|exists:Categories,IdCategorie',
            'Descriptions' => 'nullable|string',
            'DateDebut' => 'required|date',
            'DateFin' => 'required|date|after:DateDebut',
            'LienGoogleMeet' => 'nullable|string',
            'LienPhoto' => 'nullable|string',
            'LienReplay' => 'nullable|string',
            'Statut' => 'required|string|in:Publié,Dépublié',
        ]);

        $live->update($validated);

        return redirect()->back()->with('success', 'Live mis à jour avec succès.');
    }
    public function destroy($id)
    {
        Live::findOrFail($id)->delete();
        return back()->with('success', 'Live supprimé.');
    }
}

