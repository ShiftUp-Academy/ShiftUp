<?php

namespace App\Http\Controllers;

use App\Models\Offre;
use App\Models\ProgrammeFormation;
use App\Models\TypeDeCoaching;
use App\Models\DisponibiliteCoaching;
use App\Models\Categorie;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Utilisateur;
use App\Notifications\NouveauContenuNotification;
use Illuminate\Support\Facades\Notification;

class OffreController extends Controller
{
    public function index()
    {
        $offres = Offre::with(['programmes.programme', 'coachings.coaching'])
            ->orderBy('DateCreation', 'desc')
            ->get();

        $programmes = ProgrammeFormation::where('Statut', 'Publié')->get();
        $coachings = TypeDeCoaching::where('Statut', 'Publié')->get();

        return Inertia::render('PagesAdmin/AdminOffres', [
            'offres' => $offres,
            'programmes' => $programmes,
            'coachings' => $coachings
        ]);
    }

    public function publicIndex()
    {
        $offres = Offre::where('Statut', 'Publié')
            ->with(['programmes.programme', 'coachings.coaching'])
            ->orderBy('DateCreation', 'desc')
            ->get();

        $availabilities = DisponibiliteCoaching::where('EstReserve', false)
            ->whereRaw("CONCAT(\"DateDisponible\", ' ', \"HeureDebut\") > ?", [now()])
            ->orderBy('DateDisponible', 'asc')
            ->orderBy('HeureDebut', 'asc')
            ->get();

        $categories = Categorie::where('Statut', 'Publié')->get();

        // Getting all coaching types might be needed if the modal expects a list, 
        // though strictly we only need those in the offers. 
        // But for safety/completeness of the modal props:
        $typesCoachings = TypeDeCoaching::where('Statut', 'Publié')->get();

        return Inertia::render('Offres', [
            'offres' => $offres,
            'availabilities' => $availabilities,
            'categories' => $categories,
            'typesCoachings' => $typesCoachings
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Titre' => 'required|string|max:255',
            'Descriptions' => 'nullable|string',
            'LienPhoto' => 'nullable|string',
            'ReductionGlobal' => 'nullable|numeric|min:0|max:100',
            'DureeJours' => 'nullable|integer|min:0',
            'Statut' => 'required|string|in:Publié,Dépublié',
            'SelectedProgrammes' => 'array',
            'SelectedCoachings' => 'array',
        ]);

        $offre = Offre::create($validated);

        if ($request->has('SelectedProgrammes')) {
            foreach ($request->SelectedProgrammes as $prog) {
                $offre->programmes()->create([
                    'IdProgrammeFormation' => $prog['id'],
                    'ReductionSpecifique' => $prog['reduction'] ?? null
                ]);
            }
        }

        if ($request->has('SelectedCoachings')) {
            foreach ($request->SelectedCoachings as $coach) {
                $offre->coachings()->create([
                    'IdTypeCoaching' => $coach['id'],
                    'ReductionSpecifique' => $coach['reduction'] ?? null
                ]);
            }
        }

        // Notification aux utilisateurs
        if ($offre->Statut === 'Publié') {
            $users = Utilisateur::where('Role', '!=', 'admin')->get();
            Notification::send($users, new NouveauContenuNotification($offre, 'Offre'));
        }

        return back()->with('success', 'Offre créée avec succès.');
    }

    public function update(Request $request, $id)
    {
        $offre = Offre::findOrFail($id);

        $validated = $request->validate([
            'Titre' => 'required|string|max:255',
            'Descriptions' => 'nullable|string',
            'LienPhoto' => 'nullable|string',
            'ReductionGlobal' => 'nullable|numeric|min:0|max:100',
            'DureeJours' => 'nullable|integer|min:0',
            'Statut' => 'required|string|in:Publié,Dépublié',
            'SelectedProgrammes' => 'array',
            'SelectedCoachings' => 'array',
        ]);

        $wasPublished = $offre->Statut === 'Publié';
        $offre->update($validated);

        if ($offre->Statut === 'Publié' && !$wasPublished) {
            $users = Utilisateur::where('Role', '!=', 'admin')->get();
            Notification::send($users, new NouveauContenuNotification($offre, 'Offre'));
        }

        $offre->programmes()->delete();
        if ($request->has('SelectedProgrammes')) {
            foreach ($request->SelectedProgrammes as $prog) {
                $offre->programmes()->create([
                    'IdProgrammeFormation' => $prog['id'],
                    'ReductionSpecifique' => $prog['reduction'] ?? null
                ]);
            }
        }

        $offre->coachings()->delete();
        if ($request->has('SelectedCoachings')) {
            foreach ($request->SelectedCoachings as $coach) {
                $offre->coachings()->create([
                    'IdTypeCoaching' => $coach['id'],
                    'ReductionSpecifique' => $coach['reduction'] ?? null
                ]);
            }
        }

        return back()->with('success', 'Offre mise à jour.');
    }

    public function toggleStatus(Request $request, $id)
    {
        $offre = Offre::findOrFail($id);
        $offre->update(['Statut' => $request->Statut]);
        return back()->with('success', 'Statut mis à jour.');
    }

    public function destroy($id)
    {
        Offre::findOrFail($id)->delete();
        return back()->with('success', 'Offre supprimée.');
    }
}
