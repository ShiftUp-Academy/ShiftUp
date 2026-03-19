<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Cache;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $this->getCachedUser($request),
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'warning' => $request->session()->get('warning'),
                'info' => $request->session()->get('info'),
                'Bonjour' => $request->session()->get('Bonjour'),
                'new_achievements' => $request->session()->get('new_achievements'),
            ],
            'locale' => app()->getLocale(),
            'translations' => Cache::store('file')->remember('translations_' . app()->getLocale(), 86400, function () {
                return array_merge(
                    is_file(lang_path(app()->getLocale() . '.json')) ? json_decode(file_get_contents(lang_path(app()->getLocale() . '.json')), true) : [],
                    is_file(lang_path('php_' . app()->getLocale() . '.php')) ? include lang_path('php_' . app()->getLocale() . '.php') : []
                );
            }),
            'discoveryItems' => Cache::store('file')->remember('discovery_items', 3600, function () {
                $items = collect();
                
                // Fetch random programmes
                $programmes = \App\Models\ProgrammeFormation::where('Statut', 'Publié')->inRandomOrder()->take(2)->get();
                foreach($programmes as $p) {
                    $items->push(['category' => 'Programme', 'title' => $p->Titre, 'image' => $p->LienPhoto]);
                }

                // Fetch random lives
                $lives = \App\Models\Live::where('Statut', 'Publié')->inRandomOrder()->take(2)->get();
                foreach($lives as $l) {
                    $items->push(['category' => 'Live', 'title' => $l->Titre, 'image' => $l->LienPhoto]);
                }

                // Fetch random offres
                $offres = \App\Models\Offre::where('Statut', 'Publié')->inRandomOrder()->take(2)->get();
                foreach($offres as $o) {
                    $items->push(['category' => 'Offre', 'title' => $o->Titre, 'image' => $o->LienPhoto]);
                }

                return $items->toArray();
            }),
        ]);
    }

    private function getCachedUser(Request $request)
    {
        $user = $request->user();
        if (!$user) return null;

        $cacheKey = 'auth_user_' . $user->IdUtilisateur;
        
        return Cache::store('file')->remember($cacheKey, 600, function () use ($user) {
            return $user->load(['profil.reseauxSociaux']);
        });
    }
}
