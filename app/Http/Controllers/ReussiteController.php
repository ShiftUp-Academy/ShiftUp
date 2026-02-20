<?php

namespace App\Http\Controllers;

use App\Models\Reussite;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class ReussiteController extends Controller
{
    public function index()
    {
        $reussiteService = app(\App\Services\ReussiteService::class);
        $reussites = Reussite::with('utilisateurs')->orderBy('created_at', 'desc')->get();
        
        $reussites->each(function($reussite) use ($reussiteService) {
            // Context title for admin is harder because it's many. 
            // We only resolve if it's a fixed badge with valeur_requise
            $reussite->context_title = $reussiteService->resolveContextTitle($reussite);
        });

        return Inertia::render('PagesAdmin/AdminReussite', [
            'reussites' => $reussites
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_link' => 'required|string',
            'type_action' => 'required|in:lecon_terminee,etape_passee,seuil_points,reservation_evenement,temoignage_laisse,offre_achetee,autre',
            'seuil_points' => 'nullable|integer',
            'points_recompense' => 'nullable|integer',
            'est_actif' => 'boolean',
            'valeur_requise' => 'nullable|array' // or json string depending on how it's sent
        ]);

        Reussite::create($validated);

        return redirect()->back()->with('success', 'Réussite créée avec succès.');
    }

    public function update(Request $request, $id)
    {
        $reussite = Reussite::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_link' => 'required|string',
            'type_action' => 'required|in:lecon_terminee,etape_passee,seuil_points,reservation_evenement,temoignage_laisse,offre_achetee,autre',
            'seuil_points' => 'nullable|integer',
            'points_recompense' => 'nullable|integer',
            'est_actif' => 'boolean',
            'valeur_requise' => 'nullable|array'
        ]);

        $reussite->update($validated);

        return redirect()->back()->with('success', 'Réussite mise à jour.');
    }

    public function destroy($id)
    {
        $reussite = Reussite::findOrFail($id);
        $reussite->delete();

        return redirect()->back()->with('success', 'Réussite supprimée.');
    }

    public function userReussites(Request $request)
    {
        $user = $request->user();
        
        // Sync all achievements automatically on visit (Retroactive check)
        $reussiteService = app(\App\Services\ReussiteService::class);
        $reussiteService->syncAllAchievements($user);
        
        $userReussites = $user->badges()->get();

        $userReussites->each(function($reussite) use ($reussiteService) {
            $reussite->context_title = $reussiteService->resolveContextTitleByParams($reussite->type_action, $reussite->pivot->context_id)
                                       ?? $reussiteService->resolveContextTitle($reussite);
        });

        return Inertia::render('UserReussites', [
            'reussitesObtenues' => $userReussites,
            'totalPoints' => $user->Points ?? 0
        ]);
    }
}
