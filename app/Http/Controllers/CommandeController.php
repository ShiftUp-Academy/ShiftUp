<?php

namespace App\Http\Controllers;

use App\Models\Panier;
use App\Models\PanierItem;
use App\Models\ProgrammeFormation;
use App\Models\Offre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CommandeController extends Controller
{
    /**
     * Affiche la page du panier avec ses articles.
     */
    public function viewPanier()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $panier = Panier::with(['items.programme', 'items.offre'])
            ->where('IdUtilisateur', $user->IdUtilisateur)
            ->where('Statut', 'actif')
            ->first();

        return Inertia::render('Panier', [
            'panier' => $panier
        ]);
    }

    /**
     * Ajoute un programme ou une offre au panier.
     */
    public function ajouterAuPanier(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vous devez être connecté pour ajouter au panier.');
        }

        $user = Auth::user();

        $request->validate([
            'id' => 'required|integer',
            'type' => 'required|string|in:programme,offre',
            'prix' => 'required|numeric'
        ]);

        // Récupérer ou créer le panier actif
        $panier = Panier::firstOrCreate(
            ['IdUtilisateur' => $user->IdUtilisateur, 'Statut' => 'actif']
        );

        $itemIdField = ($request->type === 'programme') ? 'IdProgrammeFormation' : 'IdOffre';

        // Éviter les doublons
        $exists = PanierItem::where('IdPanier', $panier->IdPanier)
            ->where($itemIdField, $request->id)
            ->exists();

        if (!$exists) {
            PanierItem::create([
                'IdPanier' => $panier->IdPanier,
                $itemIdField => $request->id,
                'PrixAuMomentDeLAjout' => $request->prix
            ]);
        }

        return back()->with('success', 'Article ajouté au panier avec succès.');
    }

    /**
     * Retire un article du panier.
     */
    public function supprimerDuPanier($id)
    {
        $item = PanierItem::find($id);
        
        if ($item) {
            // Vérifier que le panier appartient bien à l'utilisateur
            $panier = Panier::find($item->IdPanier);
            if ($panier && $panier->IdUtilisateur === Auth::id()) {
                $item->delete();
                return back()->with('success', 'Article retiré du panier.');
            }
        }

        return back()->with('error', 'Impossible de retirer cet article.');
    }
}
