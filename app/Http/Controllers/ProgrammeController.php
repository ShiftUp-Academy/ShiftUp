<?php

namespace App\Http\Controllers;

use App\Models\ProgrammeFormation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Models\Utilisateur;
use App\Models\ReponseEtapeUtilisateur;
use App\Models\DetailReponseUtilisateur;
use App\Models\Etape;
use App\Models\Live;
use App\Models\Consultation;
use App\Models\Offre;
use App\Models\Temoignage;
use App\Models\TypeDeCoaching;
use App\Models\ReponseConsultation;
use App\Models\Categorie;
use App\Models\Reussite;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NouveauContenuNotification;
use App\Notifications\StepValidationNotification;

class ProgrammeController extends Controller
{
    public function index()
    {
        $programmes = ProgrammeFormation::with(['auteur', 'themes' => function ($query) {
                $query->orderBy('IdTheme', 'asc');
            }, 'themes.lecons' => function ($query) {
                $query->orderBy('IdLecon', 'asc');
            }, 'themes.lecons.etapes' => function ($query) {
                $query->orderBy('IdEtape', 'asc');
            }, 'themes.lecons.etapes.questions' => function ($query) {
                $query->orderBy('IdQuestion', 'asc');
            }, 'themes.lecons.etapes.questions.options' => function ($query) {
                $query->orderBy('IdOption', 'asc');
            }])
            ->where(function ($query) {
                $query->where('Type', '!=', 'Seminaire')
                    ->orWhereNull('Type')
                    ->orWhere(function ($q) {
                        $q->where('Type', 'Seminaire')
                            ->where(function ($sub) {
                                $sub->where('DateSeminaire', '>', now()->toDateString())
                                    ->orWhere(function ($subTime) {
                                        $subTime->where('DateSeminaire', now()->toDateString())
                                            ->where('HeureSeminaire', '>', now()->format('H:i'));
                                    });
                            });
                    });
            })
            ->orderBy('DateCreation', 'desc')
            ->get();
        
        $categories = \App\Models\Categorie::where('Statut', 'Publié')->get();

        return Inertia::render('PagesAdmin/AdminProgrammes', [
            'programmes' => $programmes,
            'categories' => $categories,
            'pendingValidationsCount' => \App\Models\ReponseEtapeUtilisateur::where('StatutValidation', 'Attente')->count()
        ]);
    }

    public function publicList()
    {
        $programmes = ProgrammeFormation::where('Statut', 'Publié')
            ->where(function ($query) {
                $query->where('Type', '!=', 'Seminaire')
                    ->orWhereNull('Type')
                    ->orWhere(function ($q) {
                        $q->where('Type', 'Seminaire')
                            ->where(function ($sub) {
                                $sub->where('DateSeminaire', '>', now()->toDateString())
                                    ->orWhere(function ($subTime) {
                                        $subTime->where('DateSeminaire', now()->toDateString())
                                            ->where('HeureSeminaire', '>', now()->format('H:i'));
                                    });
                            });
                    });
            })
            ->with(['auteur', 'lecons' => function ($query) {
                $query->where('Statut', 'Publié')->select('IdLecon', 'IdProgramme');
            }])
            ->orderBy('DateCreation', 'desc')
            ->get();

        $userId = Auth::id();
        $userAvancements = $userId ? \App\Models\Avancement::where('IdUtilisateur', $userId)
            ->where('EntiteType', \App\Models\Lecon::class)
            ->where('EstTermine', true)
            ->pluck('EntiteId')
            ->toArray() : [];

        $programmes->each(function ($p) use ($userAvancements) {
            $lessonIds = $p->lecons->pluck('IdLecon')->toArray();
            $total = count($lessonIds);
            if ($total === 0) {
                $p->progression = 0;
            } else {
                $completed = count(array_intersect($lessonIds, $userAvancements));
                $p->progression = round(($completed / $total) * 100);
            }
            // Remove lecons to keep response light
            unset($p->lecons);
        });

        $myConsultations = \App\Models\Consultation::where('IdUtilisateur', \Auth::id())
            ->with(['categorie', 'reponseConsultations.questions.utilisateur.profil'])
            ->orderBy('DateCreation', 'desc')
            ->get();

        $responsesQuery = \App\Models\ReponseConsultation::with(['categorie', 'questions.utilisateur.profil']);
        
        if (\Auth::check()) {
        } else {
            $responsesQuery->where('Statut', 'Publié');
        }

        $publishedConsultations = $responsesQuery->orderBy('DateCreation', 'desc')->get();

        $categories = \App\Models\Categorie::where('Statut', 'Publié')->get();
        
        $offres = \App\Models\Offre::where('Statut', 'Publié')
            ->with(['programmes.programme', 'coachings.coaching'])
            ->orderBy('DateCreation', 'desc')
            ->get();

        return Inertia::render('TouteCategorie', [
            'programmes' => $programmes,
            'offres' => $offres,
            'myConsultations' => $myConsultations,
            'publishedConsultations' => $publishedConsultations,
            'categories' => $categories
        ]);
    }

    public function show($id)
    {
        $programme = ProgrammeFormation::where('Statut', 'Publié')
            ->with(['auteur', 'themes' => function ($query) {
                $query->where('Statut', 'Publié')->orderBy('IdTheme', 'asc');
            }, 'themes.lecons' => function ($query) {
                $query->where('Statut', 'Publié')->orderBy('IdLecon', 'asc');
            }, 'themes.lecons.etapes' => function ($query) {
                $query->where('Statut', 'Publié')->orderBy('IdEtape', 'asc');
            }, 'themes.lecons.etapes.questions' => function ($query) {
                $query->orderBy('IdQuestion', 'asc');
            }, 'themes.lecons.etapes.questions.options' => function ($query) {
                $query->orderBy('IdOption', 'asc');
            }])
            ->findOrFail($id);

        $programme->themes->each(function($theme) {
            $theme->lecons->each(function($lesson) {
                if ($lesson->TypeLecon === 'PDF' && $lesson->Contenu && str_starts_with($lesson->Contenu, '/storage/')) {
                     $lesson->Contenu = asset($lesson->Contenu);
                }
            });
        });

        $userId = Auth::id();
        $lessonProgress = [];
        
        if ($userId) {
            $lessonProgress = \App\Models\Avancement::where('IdUtilisateur', $userId)
                ->where('EntiteType', \App\Models\Lecon::class)
                ->select('EntiteId', 'EstTermine', 'DateOuverture')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->EntiteId => [
                        'EstTermine' => $item->EstTermine,
                        'DateOuverture' => $item->DateOuverture ? $item->DateOuverture->toIso8601String() : null,
                    ]];
                })->toArray();
        }

        $categories = \App\Models\Categorie::where('Statut', 'Publié')->get();

        return Inertia::render('ProgrammeDetail', [
            'program' => $programme,
            'lessonProgress' => $lessonProgress,
            'categories' => $categories
        ]);
    }

    public function showSeminaire($id)
    {
        $seminaire = ProgrammeFormation::where('Statut', 'Publié')
            ->where('Type', 'Seminaire')
            ->findOrFail($id);

        return Inertia::render('SeminaireDetail', [
            'seminaire' => $seminaire
        ]);
    }

    public function showLessonContent($id, Request $request)
    {
        $lesson = \App\Models\Lecon::findOrFail($id);
        
        if (!$lesson->Contenu) {
            abort(404);
        }

        if (str_starts_with($lesson->Contenu, '/storage/')) {
            $relativePath = str_replace('/storage/', '', $lesson->Contenu);
            
            if (Storage::disk('public')->exists($relativePath)) {
                if ($request->has('download')) {
                    return response()->download(Storage::disk('public')->path($relativePath));
                }

                return redirect($lesson->Contenu);
            } else {
                Log::warning("PDF file not found in storage: " . $relativePath);
            }
        }

        return redirect($lesson->Contenu);
    }

    public function markProgress(Request $request)
    {
        $request->validate([
            'entite_id' => 'required|integer',
            'entite_type' => 'required|string|in:Programme,Theme,Lecon,Etape',
            'est_termine' => 'required|boolean'
        ]);

        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $typeMap = [
            'Programme' => ProgrammeFormation::class,
            'Theme' => \App\Models\Theme::class,
            'Lecon' => \App\Models\Lecon::class,
            'Etape' => Etape::class,
        ];

        $modelClass = $typeMap[$request->entite_type] ?? null;

        if (!$modelClass) {
            return response()->json(['message' => 'Invalid entity type'], 400);
        }

        $avancement = \App\Models\Avancement::updateOrCreate(
            [
                'IdUtilisateur' => $userId,
                'EntiteId' => $request->entite_id,
                'EntiteType' => $modelClass
            ],
            [
                'EstTermine' => $request->est_termine,
                'DateCompletion' => $request->est_termine ? now() : null
            ]
        );

        if ($request->est_termine) {
            $user = Utilisateur::find($userId);
            $reussiteService = app(\App\Services\ReussiteService::class);
            
            $actionType = null;
            $valeurs = [];
            
            if ($request->entite_type === 'Lecon') {
                $actionType = 'lecon_terminee';
                $valeurs = ['lesson_id' => $request->entite_id];
            } elseif ($request->entite_type === 'Etape') {
                $actionType = 'etape_passee';
                $valeurs = ['step_id' => $request->entite_id];
            }
            
            if ($actionType) {
                $reussiteService->checkAndUnlock($user, $actionType, $valeurs);
            }
        }

        if ($request->header('X-Inertia') || !$request->wantsJson()) {
            return back();
        }

        return response()->json(['success' => true, 'data' => $avancement]);
    }

    public function recordOpening(Request $request)
    {
        $request->validate([
            'lecon_id' => 'required|exists:Lecons,IdLecon',
        ]);

        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $avancement = \App\Models\Avancement::firstOrCreate(
            [
                'IdUtilisateur' => $userId,
                'EntiteId' => $request->lecon_id,
                'EntiteType' => \App\Models\Lecon::class
            ],
            [
                'DateOuverture' => now(),
                'EstTermine' => false
            ]
        );

        if (!$avancement->DateOuverture) {
            $avancement->update(['DateOuverture' => now()]);
        }

        return response()->json(['success' => true, 'data' => $avancement]);
    }

    public function home()
    {
        $cache = \Illuminate\Support\Facades\Cache::store('file');

        $programmes = $cache->remember('home_programmes', 300, function () {
            return ProgrammeFormation::where('Statut', 'Publié')
                ->where(function ($query) {
                    $query->where('Type', '!=', 'Seminaire')
                        ->orWhereNull('Type')
                        ->orWhere(function ($q) {
                            $q->where('Type', 'Seminaire')
                                ->where(function ($sub) {
                                    $sub->where('DateSeminaire', '>', now()->toDateString())
                                        ->orWhere(function ($subTime) {
                                            $subTime->where('DateSeminaire', now()->toDateString())
                                                ->where('HeureSeminaire', '>', now()->format('H:i'));
                                        });
                                });
                        });
                })
                ->with(['lecons' => function ($query) {
                    $query->where('Statut', 'Publié')->select('IdLecon', 'IdProgramme');
                }])
                ->orderBy('DateCreation', 'desc')
                ->get();
        });

        $userId = Auth::id();
        $userAvancements = $userId ? \App\Models\Avancement::where('IdUtilisateur', $userId)
            ->where('EntiteType', \App\Models\Lecon::class)
            ->where('EstTermine', true)
            ->pluck('EntiteId')
            ->toArray() : [];

        $programmes->each(function ($p) use ($userAvancements) {
            $lessonIds = $p->lecons->pluck('IdLecon')->toArray();
            $total = count($lessonIds);
            if ($total === 0) {
                $p->progression = 0;
            } else {
                $completed = count(array_intersect($lessonIds, $userAvancements));
                $p->progression = round(($completed / $total) * 100);
            }
            unset($p->lecons);
        });
            
        $temoignages = $cache->remember('home_temoignages', 300, function () {
            return \App\Models\Temoignage::where('Type', 'Texte')
                ->where('Statut', 'Publié')
                ->with(['utilisateur.profil'])
                ->orderBy('DateCreation', 'desc')
                ->get();
        });

        $heroVideo = $cache->remember('home_hero_video', 300, function () {
            return \App\Models\HeropageVideo::first()?->video_url;
        });
        
        $homeLives = $cache->remember('home_lives', 300, function () {
            return \App\Models\Live::where('Statut', 'Publié')
                ->whereNotNull('LienReplay')
                ->with('categorie')
                ->orderBy('DateDebut', 'desc')
                ->get();
        });
            
        $categories = $cache->remember('home_categories', 300, function () {
            return \App\Models\Categorie::all();
        });

        return Inertia::render('Home', [
            'programmes' => $programmes,
            'temoignages' => $temoignages,
            'heroVideo' => $heroVideo,
            'homeLives' => $homeLives,
            'categories' => $categories
        ]);
    }

    public function programmesPublic()
    {
        $cache = \Illuminate\Support\Facades\Cache::store('file');

        $programmes = $cache->remember('public_programmes', 300, function () {
            return ProgrammeFormation::where('Statut', 'Publié')
                ->where(function ($query) {
                    $query->where('Type', '!=', 'Seminaire')
                        ->orWhereNull('Type')
                        ->orWhere(function ($q) {
                            $q->where('Type', 'Seminaire')
                                ->where(function ($sub) {
                                    $sub->where('DateSeminaire', '>', now()->toDateString())
                                        ->orWhere(function ($subTime) {
                                            $subTime->where('DateSeminaire', now()->toDateString())
                                                ->where('HeureSeminaire', '>', now()->format('H:i'));
                                        });
                                });
                        });
                })
                ->with(['lecons' => function ($query) {
                    $query->where('Statut', 'Publié')->select('IdLecon', 'IdProgramme');
                }])
                ->orderBy('DateCreation', 'desc')
                ->get();
        });

        $userId = Auth::id();
        $userAvancements = $userId ? \App\Models\Avancement::where('IdUtilisateur', $userId)
            ->where('EntiteType', \App\Models\Lecon::class)
            ->where('EstTermine', true)
            ->pluck('EntiteId')
            ->toArray() : [];

        $programmes->each(function ($p) use ($userAvancements) {
            $lessonIds = $p->lecons->pluck('IdLecon')->toArray();
            $total = count($lessonIds);
            if ($total === 0) {
                $p->progression = 0;
            } else {
                $completed = count(array_intersect($lessonIds, $userAvancements));
                $p->progression = round(($completed / $total) * 100);
            }
            unset($p->lecons);
        });

        $categories = $cache->remember('public_categories', 300, function () {
            return \App\Models\Categorie::where('Statut', 'Publié')->get();
        });

        return Inertia::render('Programmes', [
            'programmes' => $programmes,
            'categories' => $categories
        ]);
    }

    public function articlesConseils()
    {
        $cache = \Illuminate\Support\Facades\Cache::store('file');

        $articles = $cache->remember('public_articles', 300, function () {
            return ProgrammeFormation::where('Statut', 'Publié')
                ->where(function ($query) {
                    $query->where('Prix', 0)
                        ->orWhereNull('Prix');
                })
                ->where('Type', '!=', 'Seminaire')
                ->whereHas('lecons', function ($query) {
                    $query->where('TypeLecon', 'Texte');
                })
                ->with(['auteur', 'lecons' => function ($query) {
                    $query->where('Statut', 'Publié')->where('TypeLecon', 'Texte')->orderBy('Ordre');
                }, 'themes' => function ($query) {
                    $query->where('Statut', 'Publié')->orderBy('Ordre');
                }, 'themes.lecons' => function ($query) {
                    $query->where('Statut', 'Publié')->where('TypeLecon', 'Texte')->orderBy('Ordre');
                }])
                ->orderBy('DateCreation', 'desc')
                ->get();
        });

        $categories = $cache->remember('public_categories', 300, function () {
            return \App\Models\Categorie::where('Statut', 'Publié')->get();
        });

        return Inertia::render('ArticlesConseils', [
            'articles' => $articles,
            'categories' => $categories
        ]);
    }

    public function InsertionProgramme(Request $requete)
    {
        $valide = $requete->validate([
            'Titre' => ['required', 'string', 'max:155'],
            'Descriptions' => ['nullable', 'string'],
            'Prix' => ['nullable', 'numeric', 'min:0'],
            'IdCategorie' => ['nullable', 'exists:Categories,IdCategorie'],
            'PointsOfferts' => ['nullable', 'integer', 'min:0'],
            'LienPhoto' => ['nullable', 'string'],
            'ApercuVideo' => ['nullable', 'string'],
            'Statut' => ['required', 'string', 'in:Publié,Dépublié'],
            'StatutVerrouillageProgression' => ['nullable', 'string', 'in:Verrouillé,Libre'],
            'PhotoFile' => ['nullable', 'image', 'max:13312'], // 13MB
            'Type' => ['nullable', 'string', 'in:Formation,Seminaire'],
            'DateSeminaire' => ['nullable', 'date'],
            'HeureSeminaire' => ['nullable', 'string'],
            'LieuSeminaire' => ['nullable', 'string'],
            'NombreDeJours' => ['nullable', 'integer', 'min:1'],
            'ModaliteSeminaire' => ['nullable', 'string', 'in:En ligne,Présentiel'],
            'LienGoogleMeet' => ['nullable', 'string'],
        ]);

        $lienPhoto = $valide['LienPhoto'];

        if ($requete->hasFile('PhotoFile')) {
            $fichier = $requete->file('PhotoFile');
            $nomFichier = time() . '_' . $fichier->getClientOriginalName();
            $fichier->move(public_path('Programme'), $nomFichier);
            $lienPhoto = '/Programme/' . $nomFichier;
        }

        $programme = ProgrammeFormation::create([
            'Type' => $valide['Type'] ?? 'Formation',
            'Titre' => $valide['Titre'],
            'Descriptions' => $valide['Descriptions'],
            'Prix' => $valide['Prix'],
            'IdCategorie' => $valide['IdCategorie'] ?? null,
            'PointsOfferts' => $valide['PointsOfferts'] ?? 0,
            'LienPhoto' => $lienPhoto,
            'ApercuVideo' => $valide['ApercuVideo'] ?? null,
            'Statut' => $valide['Statut'],
            'StatutVerrouillageProgression' => $valide['StatutVerrouillageProgression'] ?? 'Libre',
            'DateSeminaire' => $valide['DateSeminaire'] ?? null,
            'HeureSeminaire' => $valide['HeureSeminaire'] ?? null,
            'LieuSeminaire' => $valide['LieuSeminaire'] ?? null,
            'NombreDeJours' => $valide['NombreDeJours'] ?? null,
            'ModaliteSeminaire' => $valide['ModaliteSeminaire'] ?? 'Présentiel',
            'LienGoogleMeet' => $valide['LienGoogleMeet'] ?? null,
            'idAuteur' => Auth::id() ?? Utilisateur::where('Role', 'admin')->first()?->IdUtilisateur,
        ]);
        
        if (($valide['Type'] ?? 'Formation') === 'Formation') {
            \App\Models\Theme::create([
                'IdProgramme' => $programme->IdProgrammeFormation,
                'Titre' => 'Chapitre 1',
                'Descriptions' => 'Introduction',
                'Statut' => 'Dépublié',
                'Ordre' => 1
            ]);
        }

        if ($programme->Statut === 'Publié') {
            $users = Utilisateur::where('Role', '!=', 'admin')->get();
            Notification::send($users, new NouveauContenuNotification($programme, $programme->Type ?? 'Programme'));
        }

        return back()->with('success', 'Le programme "' . $programme->Titre . '" a été créé avec succès.');
    }

    public function majProgramme(Request $requete, $id)
    {
        $programme = ProgrammeFormation::findOrFail($id);

        $valide = $requete->validate([
            'Titre' => ['required', 'string', 'max:155'],
            'Descriptions' => ['nullable', 'string'],
            'Prix' => ['nullable', 'numeric', 'min:0'],
            'IdCategorie' => ['nullable', 'exists:Categories,IdCategorie'],
            'PointsOfferts' => ['nullable', 'integer', 'min:0'],
            'LienPhoto' => ['nullable', 'string'],
            'ApercuVideo' => ['nullable', 'string'],
            'Statut' => ['required', 'string', 'in:Publié,Dépublié'],
            'StatutVerrouillageProgression' => ['nullable', 'string', 'in:Verrouillé,Libre'],
            'PhotoFile' => ['nullable', 'image', 'max:13312'],
            'Type' => ['nullable', 'string', 'in:Formation,Seminaire'],
            'DateSeminaire' => ['nullable', 'date'],
            'HeureSeminaire' => ['nullable', 'string'],
            'LieuSeminaire' => ['nullable', 'string'],
            'NombreDeJours' => ['nullable', 'integer', 'min:1'],
            'ModaliteSeminaire' => ['nullable', 'string', 'in:En ligne,Présentiel'],
            'LienGoogleMeet' => ['nullable', 'string'],
        ]);

        $lienPhoto = $programme->LienPhoto;
        if ($requete->hasFile('PhotoFile')) {
            if ($lienPhoto && file_exists(public_path($lienPhoto))) {
            }
            $fichier = $requete->file('PhotoFile');
            $nomFichier = time() . '_' . $fichier->getClientOriginalName();
            $fichier->move(public_path('Programme'), $nomFichier);
            $lienPhoto = '/Programme/' . $nomFichier;
        } elseif ($requete->filled('LienPhoto')) {
            $lienPhoto = $valide['LienPhoto'];
        }

        $wasPublished = $programme->Statut === 'Publié';
        
        $programme->update([
            'Type' => $valide['Type'] ?? $programme->Type,
            'Titre' => $valide['Titre'],
            'Descriptions' => $valide['Descriptions'],
            'Prix' => $valide['Prix'],
            'IdCategorie' => $valide['IdCategorie'] ?? null,
            'PointsOfferts' => $valide['PointsOfferts'] ?? 0,
            'LienPhoto' => $lienPhoto,
            'ApercuVideo' => $valide['ApercuVideo'] ?? null,
            'Statut' => $valide['Statut'],
            'StatutVerrouillageProgression' => $valide['StatutVerrouillageProgression'] ?? $programme->StatutVerrouillageProgression,
            'DateSeminaire' => $valide['DateSeminaire'] ?? null,
            'HeureSeminaire' => $valide['HeureSeminaire'] ?? null,
            'LieuSeminaire' => $valide['LieuSeminaire'] ?? null,
            'NombreDeJours' => $valide['NombreDeJours'] ?? null,
            'ModaliteSeminaire' => $valide['ModaliteSeminaire'] ?? $programme->ModaliteSeminaire,
            'LienGoogleMeet' => $valide['LienGoogleMeet'] ?? null,
            'idAuteur' => Auth::id() ?? $programme->idAuteur ?? Utilisateur::where('Role', 'admin')->first()?->IdUtilisateur,
        ]);

        if ($valide['Statut'] === 'Publié' && !$wasPublished) {
            $users = Utilisateur::where('Role', '!=', 'admin')->get();
             $programme->refresh();
            Notification::send($users, new NouveauContenuNotification($programme, $programme->Type ?? 'Programme'));
        }

        return back()->with('success', 'Le programme a été mis à jour.');
    }
    public function destroy($id)
    {
        $programme = ProgrammeFormation::findOrFail($id);

        foreach ($programme->lecons as $lecon) {
            $lecon->etapes()->delete();
            $lecon->delete();
        }      
        foreach ($programme->themes as $theme) {
             $theme->delete();
        }

        $programme->delete();

        return back()->with('success', 'Le programme et ses contenus ont été déplacés dans la corbeille.');
    }

    public function duplicate($id)
    {
        $original = ProgrammeFormation::with(['lecons.etapes', 'themes.lecons.etapes'])->findOrFail($id);
        
        $copy = $original->replicate();
        $copy->Titre = $original->Titre . ' (Copie)';
        $copy->Statut = 'Dépublié';
        $copy->DateCreation = now();
        $copy->save();
        
        foreach ($original->themes as $theme) {
            $themeCopy = $theme->replicate();
            $themeCopy->IdProgramme = $copy->IdProgrammeFormation;
            $themeCopy->save();
            
            foreach ($theme->lecons as $lecon) {
                $leconCopy = $lecon->replicate();
                $leconCopy->IdProgramme = $copy->IdProgrammeFormation;
                $leconCopy->IdTheme = $themeCopy->IdTheme;
                $leconCopy->save();

                foreach ($lecon->etapes as $etape) {
                    $etapeCopy = $etape->replicate();
                    $etapeCopy->IdLecon = $leconCopy->IdLecon;
                    $etapeCopy->save();
                }
            }
        }

        return back()->with('success', 'Le programme a été dupliqué avec succès.');
    }

    public function insertionLecon(Request $request)
    {
        $validated = $request->validate([
            'IdProgramme' => 'required|exists:ProgrammeFormation,IdProgrammeFormation',
            'IdTheme' => 'nullable|exists:Themes,IdTheme',
            'Titre' => 'required|string|max:255',
            'Descriptions' => 'nullable|string',
            'Statut' => 'required|string|in:Publié,Dépublié',
            'TypeLecon' => 'required|string|in:Vidéo,PDF,Texte',
            'SourceType' => 'nullable|string|in:Youtube,Cloudinary,Local,Lien_Externe,Interne',
            'Contenu' => 'nullable|string',
            'DelaiDrop' => 'nullable|integer',
            'NombreDePages' => 'nullable|integer',
            'PointsOfferts' => 'nullable|integer',
            'Ordre' => 'nullable|integer',
            'ContenuFile' => 'nullable|file|mimes:pdf|max:51200',
        ]);

        // Handle PDF Upload
        if ($request->hasFile('ContenuFile') && $validated['TypeLecon'] === 'PDF') {
            $file = $request->file('ContenuFile');
            $path = $file->store('lessons/pdfs', 'public');
            $validated['Contenu'] = '/storage/' . $path;
        } else if ($validated['TypeLecon'] === 'PDF' && !$request->has('Contenu')) {
            $validated['Contenu'] = null;
        } else if ($validated['TypeLecon'] !== 'PDF' && $request->hasFile('ContenuFile')) {
            $validated['Contenu'] = $request->input('Contenu');
        }


        if (empty($validated['IdTheme'])) {
            $theme = \App\Models\Theme::where('IdProgramme', $validated['IdProgramme'])->first();
            if (!$theme) {
                $theme = \App\Models\Theme::create([
                    'IdProgramme' => $validated['IdProgramme'],
                    'Titre' => 'Chapitre 1',
                    'Descriptions' => 'Premier chapitre du programme.',
                    'Statut' => 'Dépublié',
                    'Ordre' => 1
                ]);
            }
            $validated['IdTheme'] = $theme->IdTheme;
        }

        if (empty($validated['Ordre'])) {
             $maxOrdre = \App\Models\Lecon::where('IdTheme', $validated['IdTheme'])->max('Ordre');
             $validated['Ordre'] = $maxOrdre ? $maxOrdre + 1 : 1;
        }

        \App\Models\Lecon::create($validated);

        return back()->with('success', 'La leçon a été ajoutée avec succès.');
    }

    public function miseAJourLecon(Request $request, $id)
    {
        $lesson = \App\Models\Lecon::findOrFail($id);
        
        $validated = $request->validate([
            'Titre' => 'required|string|max:255',
            'Descriptions' => 'nullable|string',
            'Statut' => 'required|string|in:Publié,Dépublié',
            'TypeLecon' => 'required|string|in:Vidéo,PDF,Texte',
            'SourceType' => 'nullable|string|in:Youtube,Cloudinary,Local,Lien_Externe,Interne',
            'Contenu' => 'nullable|string',
            'DelaiDrop' => 'nullable|integer',
            'NombreDePages' => 'nullable|integer',
            'PointsOfferts' => 'nullable|integer',
            'Ordre' => 'nullable|integer',
            'ContenuFile' => 'nullable|file|mimes:pdf|max:51200', // 50MB max for PDF
        ]);

        // Handle PDF Upload
        if ($request->hasFile('ContenuFile') && $validated['TypeLecon'] === 'PDF') {
            // Delete old PDF file if it exists
            if ($lesson->TypeLecon === 'PDF' && $lesson->Contenu && str_starts_with($lesson->Contenu, '/storage/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $lesson->Contenu));
            }

            $file = $request->file('ContenuFile');
            $path = $file->store('lessons/pdfs', 'public');
            $validated['Contenu'] = '/storage/' . $path;
        } else if ($validated['TypeLecon'] === 'PDF' && !$request->has('Contenu')) {
            unset($validated['Contenu']);
        } else if ($validated['TypeLecon'] !== 'PDF' && $lesson->TypeLecon === 'PDF' && $lesson->Contenu && str_starts_with($lesson->Contenu, '/storage/')) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $lesson->Contenu));
        }

        $lesson->update($validated);

        return back()->with('success', 'La leçon a été mise à jour avec succès.');
    }

    public function supprimerLecon($id)
    {
        $lesson = \App\Models\Lecon::findOrFail($id);
        
        if ($lesson->TypeLecon === 'PDF' && $lesson->Contenu && str_starts_with($lesson->Contenu, '/storage/')) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $lesson->Contenu));
        }

        $lesson->etapes()->delete();
        $lesson->delete();

        return back()->with('success', 'La leçon a été supprimée.');
    }

    public function dupliquerLecon($id)
    {
        $original = \App\Models\Lecon::with('etapes')->findOrFail($id);
        
        $copy = $original->replicate();
        $copy->Titre = $original->Titre . ' (Copie)';
        $copy->save();

        foreach ($original->etapes as $etape) {
            $etapeCopy = $etape->replicate();
            $etapeCopy->IdLecon = $copy->IdLecon;
            $etapeCopy->save();
        }

        return back()->with('success', 'La leçon a été dupliquée avec succès.');
    }

    public function insertionTheme(Request $request)
    {
        $validated = $request->validate([
            'IdProgramme' => 'required|exists:ProgrammeFormation,IdProgrammeFormation',
            'Titre' => 'required|string|max:255',
            'Descriptions' => 'nullable|string',
            'Statut' => 'required|string|in:Publié,Dépublié',
            'Ordre' => 'nullable|integer'
        ]);

        \App\Models\Theme::create($validated);
        return back()->with('success', 'Le chapitre a été ajouté.');
    }

    public function majTheme(Request $request, $id)
    {
        $theme = \App\Models\Theme::findOrFail($id);
        $validated = $request->validate([
            'Titre' => 'required|string|max:255',
            'Descriptions' => 'nullable|string',
            'Statut' => 'required|string|in:Publié,Dépublié',
            'Ordre' => 'nullable|integer'
        ]);

        $theme->update($validated);
        return back()->with('success', 'Le chapitre a été mis à jour.');
    }

    public function supprimerTheme($id)
    {
        $theme = \App\Models\Theme::findOrFail($id);
        $theme->delete();
        return back()->with('success', 'Le chapitre a été supprimé.');
    }

    public function insertionEtape(Request $request)
    {
        $validated = $request->validate([
            'IdLecon' => 'required|exists:Lecons,IdLecon',
            'Titre' => 'required|string|max:255',
            'Descriptions' => 'nullable|string',
            'Statut' => 'required|string|in:Publié,Dépublié',
            'TypeEtape' => 'required|string|in:Quiz,QuestionReponse,Cocher',
            'Ordre' => 'nullable|integer',
            'PointsOfferts' => 'nullable|integer',
            'NecessiteValidationAdmin' => 'required|boolean',
            'Question' => 'nullable|string|max:255',
            'Options' => 'nullable|array'
        ]);

        if (empty($validated['Ordre'])) {
             $maxOrdre = \App\Models\Etape::where('IdLecon', $validated['IdLecon'])->max('Ordre');
             $validated['Ordre'] = $maxOrdre ? $maxOrdre + 1 : 1;
        }

        $etape = \App\Models\Etape::create($validated);

        if (!empty($validated['Question'])) {
            $typeQuestion = 'Unique';
            if ($validated['TypeEtape'] === 'Cocher') $typeQuestion = 'Multiple';
            if ($validated['TypeEtape'] === 'QuestionReponse') $typeQuestion = 'Ouverte';

            $question = \App\Models\QuestionEtape::create([
                'IdEtape' => $etape->IdEtape,
                'Intitule' => $validated['Question'],
                'TypeQuestion' => $typeQuestion,
                'Ordre' => 1
            ]);

            if (!empty($validated['Options']) && $validated['TypeEtape'] !== 'QuestionReponse') {
                foreach ($validated['Options'] as $index => $opt) {
                    \App\Models\OptionQuestion::create([
                        'IdQuestion' => $question->IdQuestion,
                        'TexteOption' => $opt['texte'],
                        'EstCorrecte' => $opt['est_correcte'],
                        'Ordre' => $index + 1
                    ]);
                }
            }
        }

        return back()->with('success', 'L\'étape a été ajoutée.');
    }

    public function majEtape(Request $request, $id)
    {
        $etape = \App\Models\Etape::findOrFail($id);
        
        $validated = $request->validate([
            'Titre' => 'required|string|max:255',
            'Descriptions' => 'nullable|string',
            'Statut' => 'required|string|in:Publié,Dépublié',
            'TypeEtape' => 'required|string|in:Quiz,QuestionReponse,Cocher',
            'Ordre' => 'nullable|integer',
            'PointsOfferts' => 'nullable|integer',
            'NecessiteValidationAdmin' => 'required|boolean',
            'Question' => 'nullable|string|max:255',
            'Options' => 'nullable|array'
        ]);

        $etape->update($validated);
        $etape->questions()->delete(); 

        if (!empty($validated['Question'])) {
            $typeQuestion = 'Unique';
            if ($validated['TypeEtape'] === 'Cocher') $typeQuestion = 'Multiple';
            if ($validated['TypeEtape'] === 'QuestionReponse') $typeQuestion = 'Ouverte';

            $question = \App\Models\QuestionEtape::create([
                'IdEtape' => $etape->IdEtape,
                'Intitule' => $validated['Question'],
                'TypeQuestion' => $typeQuestion,
                'Ordre' => 1
            ]);

            if (!empty($validated['Options']) && $validated['TypeEtape'] !== 'QuestionReponse') {
                foreach ($validated['Options'] as $index => $opt) {
                    \App\Models\OptionQuestion::create([
                        'IdQuestion' => $question->IdQuestion,
                        'TexteOption' => $opt['texte'],
                        'EstCorrecte' => $opt['est_correcte'],
                        'Ordre' => $index + 1
                    ]);
                }
            }
        }

        return back()->with('success', 'L\'étape a été mise à jour.');
    }





    public function supprimerEtape($id)
    {
        $etape = \App\Models\Etape::findOrFail($id);
        $etape->delete();
        return back()->with('success', 'L\'étape a été supprimée.');
    }




    public function trashData()
    {
        $deletedPrograms = ProgrammeFormation::onlyTrashed()
            ->with(['auteur'])
            ->get()
            ->map(function ($p) {
                $p->trash_status = 'deleted_program';
                $p->display_title = $p->Titre;
                return $p;
            });

        $programsWithDeletedLessons = ProgrammeFormation::whereNull('deleted_at')
            ->whereHas('lecons', function ($q) {
                $q->onlyTrashed();
            })
            ->with(['lecons' => function ($q) {
                $q->onlyTrashed();
            }])
            ->get()
            ->map(function ($p) {
                $p->trash_status = 'has_deleted_content';
                $p->deleted_lessons_count = $p->lecons->count();
                $p->display_title = $p->Titre;
                return $p;
            });

        $programsWithDeletedThemes = ProgrammeFormation::whereNull('deleted_at')
            ->whereHas('themes', function ($q) {
                $q->onlyTrashed();
            })
            ->with(['themes' => function ($q) {
                $q->onlyTrashed();
            }])
            ->get()
            ->map(function ($p) {
                $p->trash_status = 'has_deleted_content';
                $p->deleted_themes_count = $p->themes->count();
                $p->display_title = $p->Titre;
                return $p;
            });
        
        $merged = $deletedPrograms
            ->concat($programsWithDeletedLessons)
            ->concat($programsWithDeletedThemes)
            ->unique('IdProgrammeFormation')
            ->values();


        $deletedLives = Live::onlyTrashed()->with('auteur')->get()->map(function($item) {
            $item->trash_type = 'live';
            $item->display_title = $item->Titre;
            return $item;
        });

        $deletedConsultations = Consultation::onlyTrashed()->with('utilisateur.profil')->get()->map(function($item) {
            $item->trash_type = 'consultation';
            $item->display_title = $item->Titre;
            return $item;
        });

        $deletedRentrees = ReponseConsultation::onlyTrashed()->with('categorie')->get()->map(function($item) {
            $item->trash_type = 'consultation_reponse';
            $item->display_title = "[Archive] " . $item->Titre;
            return $item;
        });

        $deletedConsultations = $deletedConsultations->concat($deletedRentrees);

        $deletedOffres = Offre::onlyTrashed()->get()->map(function($item) {
            $item->trash_type = 'offre';
            $item->display_title = $item->Titre;
            return $item;
        });

        $deletedTemoignages = Temoignage::onlyTrashed()->with('utilisateur.profil')->get()->map(function($item) {
            $item->trash_type = 'temoignage';
            $prenom = $item->utilisateur && $item->utilisateur->profil ? $item->utilisateur->profil->Prenom : 'Inconnu';
            $item->display_title = "Témoignage de " . $prenom;
            return $item;
        });

        $deletedCoachings = TypeDeCoaching::onlyTrashed()->get()->map(function($item) {
            $item->trash_type = 'coaching';
            $item->display_title = $item->NomDeType;
            return $item;
        });

        $deletedCategories = Categorie::onlyTrashed()->get()->map(function($item) {
            $item->trash_type = 'categorie';
            $item->display_title = "Catégorie: " . $item->Nom;
            return $item;
        });

        $deletedReussites = Reussite::onlyTrashed()->get()->map(function($item) {
            $item->trash_type = 'reussite';
            $item->display_title = "Réussite: " . $item->nom;
            return $item;
        });

        return response()->json([
            'programs' => $merged,
            'lives' => $deletedLives,
            'consultations' => $deletedConsultations,
            'offres' => $deletedOffres,
            'temoignages' => $deletedTemoignages,
            'coachings' => $deletedCoachings,
            'categories' => $deletedCategories,
            'reussites' => $deletedReussites,
        ]);
    }






    public function restore($type, $id)
    {
        switch ($type) {
            case 'program':
                $program = ProgrammeFormation::onlyTrashed()->findOrFail($id);
                $deletedAt = $program->deleted_at;
                $program->restore();
                
                 $program->lecons()->onlyTrashed()->where('deleted_at', '>=', $deletedAt)->restore();
                 $program->themes()->onlyTrashed()->where('deleted_at', '>=', $deletedAt)->restore();
                 
                return back()->with('success', 'Le programme a été restauré.');

            case 'lesson':
                $lesson = \App\Models\Lecon::onlyTrashed()->findOrFail($id);
                $deletedAt = $lesson->deleted_at;
                $lesson->restore();
                $lesson->etapes()->onlyTrashed()->where('deleted_at', '>=', $deletedAt)->restore();
                return back()->with('success', 'La leçon a été restaurée.');

            case 'theme':
                $theme = \App\Models\Theme::onlyTrashed()->findOrFail($id);
                $theme->restore();
                return back()->with('success', 'Le chapitre a été restauré.');

            case 'step':
                $step = \App\Models\Etape::onlyTrashed()->findOrFail($id);
                $step->restore();
                return back()->with('success', 'L\'étape a été restaurée.');

            case 'live':
                Live::onlyTrashed()->findOrFail($id)->restore();
                return back()->with('success', 'Le live a été restauré.');

            case 'consultation':
                Consultation::onlyTrashed()->findOrFail($id)->restore();
                return back()->with('success', 'La consultation a été restaurée.');

            case 'offre':
                Offre::onlyTrashed()->findOrFail($id)->restore();
                return back()->with('success', 'L\'offre a été restaurée.');

            case 'temoignage':
                Temoignage::onlyTrashed()->findOrFail($id)->restore();
                return back()->with('success', 'Le témoignage a été restauré.');

            case 'coaching':
                TypeDeCoaching::onlyTrashed()->findOrFail($id)->restore();
                return back()->with('success', 'Le type de coaching a été restauré.');

            case 'consultation_reponse':
                ReponseConsultation::onlyTrashed()->findOrFail($id)->restore();
                return back()->with('success', 'L\'archive de consultation a été restaurée.');

            case 'categorie':
                \App\Models\Categorie::onlyTrashed()->findOrFail($id)->restore();
                return back()->with('success', 'La catégorie a été restaurée.');

            case 'reussite':
                \App\Models\Reussite::onlyTrashed()->findOrFail($id)->restore();
                return back()->with('success', 'La réussite a été restaurée.');


            default:
                return back()->with('error', 'Type inconnu.');
        }
    }

    public function getPendingSubmissions()
    {
        $submissions = ReponseEtapeUtilisateur::with([
            'utilisateur.profil',
            'etape.lecon.theme.programme',
            'details.question'
        ])
        ->where('StatutValidation', 'Attente')
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json($submissions);
    }

    public function validateSubmission(Request $request, $id)
    {
        $submission = ReponseEtapeUtilisateur::with(['etape', 'utilisateur'])->findOrFail($id);
        
        $request->validate([
            'StatutValidation' => 'required|in:Validé,Rejeté',
            'ReponseAdmin' => 'nullable|string'
        ]);

        $submission->update([
            'StatutValidation' => $request->StatutValidation,
            'ReponseAdmin' => $request->ReponseAdmin,
            'DateValidation' => now()
        ]);

        if ($request->StatutValidation === 'Validé') {
            if ($submission->etape->PointsOfferts > 0) {
                $submission->utilisateur->increment('Points', $submission->etape->PointsOfferts);
            }

            \App\Models\Avancement::updateOrCreate(
                [
                    'IdUtilisateur' => $submission->IdUtilisateur,
                    'EntiteId' => $submission->IdEtape,
                    'EntiteType' => \App\Models\Etape::class
                ],
                [
                    'EstTermine' => true,
                    'DateCompletion' => now()
                ]
            );

            $lesson = $submission->etape->lecon;
            $allStepsCount = $lesson->etapes()->where('Statut', 'Publié')->count();
            $completedStepsCount = \App\Models\Avancement::where('IdUtilisateur', $submission->IdUtilisateur)
                ->where('EntiteType', \App\Models\Etape::class)
                ->whereIn('EntiteId', $lesson->etapes()->pluck('IdEtape'))
                ->where('EstTermine', true)
                ->count();

            if ($allStepsCount === $completedStepsCount) {
                 \App\Models\Avancement::updateOrCreate(
                    [
                        'IdUtilisateur' => $submission->IdUtilisateur,
                        'EntiteId' => $lesson->IdLecon,
                        'EntiteType' => \App\Models\Lecon::class
                    ],
                    [
                        'EstTermine' => true,
                        'DateCompletion' => now()
                    ]
                );
            }
        }

        $submission->utilisateur->notify(new StepValidationNotification(
            $submission->etape,
            $request->ReponseAdmin,
            $request->StatutValidation
        ));

        return back()->with('success', 'La réponse a été ' . strtolower($request->StatutValidation) . 'e.');
    }

    public function submitStepResponse(Request $request, $id)
    {
        $etape = Etape::findOrFail($id);
        
        $request->validate([
            'responses' => 'required|array'
        ]);

        $submission = ReponseEtapeUtilisateur::create([
            'IdUtilisateur' => Auth::id(),
            'IdEtape' => $id,
            'StatutValidation' => 'Attente'
        ]);

        foreach ($request->responses as $questionId => $responseText) {
            DetailReponseUtilisateur::create([
                'IdReponse' => $submission->IdReponse,
                'IdQuestion' => $questionId,
                'TexteReponse' => $responseText
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Votre réponse a été envoyée pour validation.',
            'submission' => $submission
        ]);
    }
}