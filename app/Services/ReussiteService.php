<?php

namespace App\Services;

use App\Models\Reussite;
use App\Models\Utilisateur;
use App\Models\UserReussite;
use Illuminate\Support\Facades\Log;

class ReussiteService
{
    protected $newlyUnlocked = [];

    public function syncAllAchievements(Utilisateur $utilisateur)
    {
        // 1. Sync Point-based achievements
        $this->checkAndUnlock($utilisateur, 'seuil_points');

        // 2. Sync Completion-based achievements (Lessons, Steps)
        $avancements = \App\Models\Avancement::where('IdUtilisateur', $utilisateur->IdUtilisateur)
            ->where('EstTermine', true)
            ->get();

        foreach ($avancements as $avancement) {
            $type = null;
            $valeurs = [];
            
            if ($avancement->EntiteType === \App\Models\Lecon::class) {
                $type = 'lecon_terminee';
                $valeurs = ['lesson_id' => $avancement->EntiteId];
            } elseif ($avancement->EntiteType === \App\Models\Etape::class) {
                $type = 'etape_passee';
                $valeurs = ['step_id' => $avancement->EntiteId];
            }

            if ($type) {
                $this->checkAndUnlock($utilisateur, $type, $valeurs);
            }
        }
    }

    public function checkAndUnlock(Utilisateur $utilisateur, string $actionType, array $valeurs = [])
    {
        $reussitesExistantes = Reussite::where('est_actif', true)
            ->where('type_action', $actionType)
            ->get();

        foreach ($reussitesExistantes as $reussite) {
            $contextType = $this->resolveContextType($actionType);
            $contextId = null;

            if ($actionType === 'lecon_terminee' && isset($valeurs['lesson_id'])) $contextId = $valeurs['lesson_id'];
            if ($actionType === 'chapitre_fini' && isset($valeurs['theme_id'])) $contextId = $valeurs['theme_id'];
            if ($actionType === 'etape_passee' && isset($valeurs['step_id'])) $contextId = $valeurs['step_id'];
            if ($actionType === 'offre_achetee' && isset($valeurs['offer_id'])) $contextId = $valeurs['offer_id'];

            // Check if user already has this badge for THIS SPECIFIC context
            $exists = \DB::table('user_reussites')
                ->where('user_id', $utilisateur->IdUtilisateur)
                ->where('reussite_id', $reussite->id)
                ->where('context_type', $contextType)
                ->where('context_id', $contextId)
                ->exists();

            if ($exists) {
                continue;
            }

            $doitDebloquer = false;

            switch ($actionType) {
                case 'seuil_points':
                    if (isset($utilisateur->Points) && $utilisateur->Points >= $reussite->seuil_points) {
                        $doitDebloquer = true;
                    }
                    break;
                    
                case 'lecon_terminee':
                case 'chapitre_fini':
                case 'etape_passee':
                case 'reservation_evenement':
                case 'temoignage_laisse':
                case 'offre_achetee':
                    $doitDebloquer = true;
                    // Si aucune valeur requise n'est définie, n'importe quelle réussite de ce type débloque (ex: n'importe quelle leçon)
                    if ($reussite->valeur_requise) {
                        foreach ($reussite->valeur_requise as $key => $val) {
                            if (!isset($valeurs[$key]) || $valeurs[$key] != $val) {
                                $doitDebloquer = false;
                                break;
                            }
                        }
                    }
                    break;

                default:
                    break;
            }

            if ($doitDebloquer) {
                $this->unlockReussite($utilisateur, $reussite, $contextType, $contextId);
            }
        }
    }

    protected function unlockReussite(Utilisateur $utilisateur, Reussite $reussite, $contextType = null, $contextId = null)
    {
        $utilisateur->badges()->attach($reussite->id, [
            'context_type' => $contextType,
            'context_id' => $contextId,
            'date_obtention' => now(),
        ]);

        if ($reussite->points_recompense > 0) {
            if (isset($utilisateur->Points)) {
                $utilisateur->Points += $reussite->points_recompense;
                $utilisateur->save();

                // Log to dedicated table
                \DB::table('historique_points')->insert([
                    'user_id' => $utilisateur->IdUtilisateur,
                    'points' => $reussite->points_recompense,
                    'raison' => "Réussite débloquée : " . $reussite->nom,
                    'reference_id' => $reussite->id,
                    'reference_type' => Reussite::class,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Attach context title for frontend display
        $reussite->context_title = $this->resolveContextTitleByParams($reussite->type_action, $contextId) 
                                   ?? $this->resolveContextTitle($reussite);

        // Store for notification
        $this->newlyUnlocked[] = $reussite;
        session()->flash('new_achievements', $this->newlyUnlocked);

        Log::info("Réussite débloquée !", ['user' => $utilisateur->IdUtilisateur, 'reussite' => $reussite->nom]);
    }

    protected function resolveContextType($actionType)
    {
        return match ($actionType) {
            'lecon_terminee' => \App\Models\Lecon::class,
            'chapitre_fini' => \App\Models\Theme::class,
            'etape_passee' => \App\Models\Etape::class,
            'offre_achetee' => \App\Models\Offre::class,
            default => null,
        };
    }

    public function resolveContextTitleByParams($typeAction, $contextId)
    {
        if (!$contextId) return null;
        try {
            if ($typeAction === 'lecon_terminee') {
                $lecon = \App\Models\Lecon::find($contextId);
                return $lecon ? $lecon->Titre : null;
            }
            if ($typeAction === 'chapitre_fini') {
                $theme = \App\Models\Theme::find($contextId);
                return $theme ? $theme->Titre : null;
            }
            if ($typeAction === 'etape_passee') {
                $etape = \App\Models\Etape::find($contextId);
                return $etape ? $etape->Titre : null;
            }
        } catch (\Exception $e) {}
        return null;
    }

    public function resolveContextTitle($reussite)
    {
        if (!$reussite->valeur_requise) return null;

        try {
            if ($reussite->type_action === 'lecon_terminee' && isset($reussite->valeur_requise['lesson_id'])) {
                $lecon = \App\Models\Lecon::find($reussite->valeur_requise['lesson_id']);
                return $lecon ? $lecon->Titre : null;
            }
            if ($reussite->type_action === 'etape_passee' && isset($reussite->valeur_requise['step_id'])) {
                $etape = \App\Models\Etape::find($reussite->valeur_requise['step_id']);
                return $etape ? $etape->Titre : null;
            }
            if ($reussite->type_action === 'offre_achetee' && isset($reussite->valeur_requise['offer_id'])) {
                $offre = \App\Models\Offre::find($reussite->valeur_requise['offer_id']);
                return $offre ? $offre->Titre : null;
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }
}
