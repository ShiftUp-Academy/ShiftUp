<?php

namespace App\Http\Controllers;

use App\Models\Temoignage;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Utilisateur;
use App\Notifications\ActiviteAdminNotification;
use Illuminate\Support\Facades\Notification;

class TemoignageController extends Controller
{
    public function index()
    {
        $temoignages = Temoignage::with(['utilisateur.profil' => function($q) {
            $q->select('IdProfil', 'IdUtilisateur', 'Prenom', 'Nom', 'PhotoProfil', 'Metier');
        }])
            ->where('Statut', 'Publié')
            ->inRandomOrder()
            ->get();
            
        return Inertia::render('Temoignage', [
            'temoignages' => $temoignages
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'Type' => 'required|in:Texte,Photo,Video',
            'ContenuTexte' => 'nullable|string',
            'CheminFichier' => 'nullable|string', // Pour les liens externes
            'Fichier' => 'nullable|file|max:51200', // 50MB max
        ]);

        $temoignage = new Temoignage();
        $temoignage->IdUtilisateur = $request->IdUtilisateur ?? auth()->id();
        $temoignage->Type = $request->Type;
        $temoignage->ContenuTexte = $request->ContenuTexte;
        $temoignage->Statut = 'Publié'; // Par défaut publié, ou 'En attente' si modération souhaitée

        if ($request->hasFile('Fichier')) {
            $path = $request->file('Fichier')->store('temoignages', 'public');
            $temoignage->CheminFichier = '/storage/' . $path;
        } elseif ($request->CheminFichier) {
            $temoignage->CheminFichier = $request->CheminFichier;
        }

        $temoignage->save();

        // Débloquer les réussites
        $reussiteService = app(\App\Services\ReussiteService::class);
        $user = \App\Models\Utilisateur::find($temoignage->IdUtilisateur);
        $reussiteService->checkAndUnlock($user, 'temoignage_laisse');

        // Notification Admin
        $admins = \App\Models\Utilisateur::where('Role', 'admin')->get();
        Notification::send($admins, new ActiviteAdminNotification(
            "Nouveau témoignage reçu (" . $request->Type . ")",
            "Temoignage",
            "/admin/temoignages"
        ));

        return redirect()->back()->with('success', 'Votre témoignage a été publié avec succès !');
    }
    public function update(Request $request, $id)
    {
        $temoignage = Temoignage::findOrFail($id);
        $temoignage->update($request->only(['Statut', 'ContenuTexte', 'Type', 'CheminFichier']));
        return redirect()->back()->with('success', 'Témoignage mis à jour.');
    }

    public function destroy($id)
    {
        $temoignage = Temoignage::findOrFail($id);
        $temoignage->delete();
        return redirect()->back()->with('success', 'Témoignage supprimé.');
    }
}
