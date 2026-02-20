<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModeratorPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect('/login');
        }

        if ($user->Role === 'admin') {
            return $next($request);
        }

        if ($user->Role !== 'moderateur') {
            return redirect('/')->with('error', 'Accès refusé.');
        }

        $route = $request->route()->getName();
        $permissions = $user->PermissionsModerateur ?? [];

        $adminOnlyRoutes = [
            'admin.utilisateurs.update-moderator',
            'admin.newsletter.send',
            'admin.verify-password',
            'admin.update-profile',
        ];

        if (in_array($route, $adminOnlyRoutes)) {
            return redirect('/admin/programmes')->with('error', 'Accès réservé aux administrateurs.');
        }

        $mapping = [
            'admin.programmes' => 'programmes',
            'admin.lecons' => 'programmes',
            'admin.etapes' => 'programmes',
            'admin.themes' => 'programmes',
            'admin.consultations' => 'consultations',
            'admin.lives' => 'lives',
            'admin.coachings' => 'coachings',
            'admin.offres' => 'offres',
            'admin.utilisateurs' => 'utilisateurs',
            'admin.newsletter' => 'utilisateurs',
            'admin.temoignages' => 'temoignages',
            'admin.categories' => 'programmes',
        ];

        foreach ($mapping as $routePattern => $permissionKey) {
            if (str_starts_with($route, $routePattern)) {
                if (!in_array($permissionKey, $permissions)) {
                    // Find first available permission to avoid redirect loop
                    foreach ($mapping as $pRoute => $pKey) {
                        if (in_array($pKey, $permissions)) {
                            return redirect()->route($pRoute)->with('error', 'Accès refusé à cette section.');
                        }
                    }
                    return redirect('/')->with('error', 'Vous n\'avez aucune permission d\'accès à l\'administration.');
                }
            }
        }

        return $next($request);
    }
}
