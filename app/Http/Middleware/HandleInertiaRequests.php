<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

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
                'user' => $request->user() ? $request->user()->load(['profil.reseauxSociaux']) : null,
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
            'translations' => array_merge(
                is_file(lang_path(app()->getLocale() . '.json')) ? json_decode(file_get_contents(lang_path(app()->getLocale() . '.json')), true) : [],
                is_file(lang_path('php_' . app()->getLocale() . '.php')) ? include lang_path('php_' . app()->getLocale() . '.php') : []
            ),
        ]);
    }
}
