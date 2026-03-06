<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Notifications\ReponseAdminNotification;
use App\Notifications\ActiviteAdminNotification;
use Illuminate\Support\Facades\Notification;
use App\Models\Utilisateur;

use App\Models\ReponseConsultation;

class ConsultationController extends Controller
{
    public function index()
    {
        $categories = \App\Models\Categorie::where('Statut', 'Publié')->get();

        
        $myConsultations = Consultation::where('IdUtilisateur', Auth::id())
            ->with(['categorie', 'reponseConsultations.questions.utilisateur.profil'])
            ->orderBy('DateCreation', 'desc')
            ->get();

        $responsesQuery = \App\Models\ReponseConsultation::with(['categorie', 'questions.utilisateur.profil']);
        
        if (!Auth::check()) {
            $responsesQuery->where('Statut', 'Publié');
        }

        $publishedConsultations = $responsesQuery->orderBy('DateCreation', 'desc')->get();

        return Inertia::render('Consultations', [
            'categories' => $categories,
            'myConsultations' => $myConsultations,
            'publishedConsultations' => $publishedConsultations
        ]);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:155',
            'question' => 'required|string',
            'category_id' => 'nullable|exists:Categories,IdCategorie',
            'lesson_id' => 'nullable|exists:Lecons,IdLecon',
            'source_type' => 'required|string|in:Lecon,Formulaire'
        ]);

        $consultation = Consultation::create([
            'IdUtilisateur' => Auth::id(),
            'Titre' => $validated['titre'],
            'Question' => $validated['question'],
            'IdCategorie' => $validated['category_id'] ?? null,
            'IdLecon' => $validated['lesson_id'] ?? null,
            'SourceType' => $validated['source_type'],
            'Statut' => 'Ouverte'
        ]);

        // Notification Admin
        $admins = Utilisateur::where('Role', 'admin')->get();
        
        \Log::info('Tentative d\'envoi de notification', [
            'nombre_admins' => $admins->count(),
            'admins' => $admins->map(fn($a) => ['id' => $a->IdUtilisateur, 'email' => $a->Email])->toArray()
        ]);
        
        if ($admins->count() > 0) {
            try {
                Notification::send($admins, new ActiviteAdminNotification(
                    "Nouvelle question posée : " . $validated['titre'],
                    "Consultation",
                    "/admin/consultations"
                ));
                \Log::info('Notification envoyée avec succès');
            } catch (\Exception $e) {
                \Log::error('Erreur lors de l\'envoi de notification: ' . $e->getMessage());
            }
        } else {
            \Log::warning('Aucun admin trouvé pour recevoir la notification');
        }

        return back()->with('success', 'Votre question a été envoyée au coach.');
    }
    public function storeResponse(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'videoUrl' => 'required|string',
            'Statut' => 'required|string|in:Publié,Dépublié',
            'category_id' => 'required|exists:Categories,IdCategorie',
            'question_ids' => 'required|array',
            'question_ids.*' => 'exists:Consultations,IdConsultation'
        ]);

        \DB::beginTransaction();
        try {
            $reponse = \App\Models\ReponseConsultation::create([
                'IdCategorie' => $validated['category_id'],
                'Titre' => $validated['nom'],
                'Descriptions' => $validated['description'],
                'LienVideo' => $validated['videoUrl'],
                'Statut' => $validated['Statut']
            ]);

            // Link questions and close them
            $reponse->questions()->attach($validated['question_ids']);
            
            $consultations = Consultation::whereIn('IdConsultation', $validated['question_ids'])->get();
            
            foreach ($consultations as $consultation) {
                $consultation->update(['Statut' => 'Fermée']);
                if ($consultation->utilisateur) {
                    $consultation->utilisateur->notify(new ReponseAdminNotification($consultation, $reponse));
                }
            }

            \DB::commit();
            return redirect()->route('admin.consultations')->with('success', 'La réponse à la consultation a été enregistrée.');
        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Erreur lors de l\'enregistrement de la réponse consultation: ' . $e->getMessage(), [
                'exception' => $e,
                'data' => $request->all()
            ]);
            return back()->with('error', 'Une erreur est survenue lors de l\'enregistrement : ' . $e->getMessage());
        }
    }
    public function updateResponse(Request $request, $id)
    {
        $reponse = \App\Models\ReponseConsultation::findOrFail($id);

        $validated = $request->validate([
            'Titre' => 'sometimes|string|max:255',
            'Descriptions' => 'nullable|string',
            'LienVideo' => 'sometimes|string',
            'Statut' => 'sometimes|string|in:Publié,Dépublié',
            'IdCategorie' => 'sometimes|exists:Categories,IdCategorie',
        ]);

        $reponse->update($validated);

        return back()->with('success', 'La consultation archive a été mise à jour.');
    }

    public function destroy($id)
    {
        Consultation::findOrFail($id)->delete();
        return back()->with('success', 'La question a été supprimée.');
    }

    public function destroyArchive($id)
    {
        ReponseConsultation::findOrFail($id)->delete();
        return back()->with('success', 'L\'archive a été supprimée.');
    }
}
