<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use App\Models\ProfilUtilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class UtilisateurController extends Controller
{

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $email = trim($credentials['email']);
        $password = $credentials['password'];

        $user = Utilisateur::where('Email', $email)->first();

        if ($user && Hash::check($password, $user->MotDePasseHash)) {
            Auth::login($user, $request->has('remember'));
            $request->session()->regenerate();

            if ($email === 'monsieuradmin@gmail.com') {
                return redirect()->intended('/admin/programmes')->with('Bonjour', 'Bienvenue Monsieur l\'Administrateur !');
            }

            return redirect()->intended('/')->with('Bonjour', 'Connexion réussie. Ravi de vous revoir!');
        }

        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
        ])->onlyInput('email');
    }

    public function inscription(Request $request)
    {
        $validated = $request->validate([
            'firstName' => ['required', 'string', 'max:100'],
            'lastName' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:Utilisateurs,Email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $role = $validated['email'] === 'monsieuradmin@gmail.com' ? 'admin' : 'utilisateur';

        $utilisateur = Utilisateur::create([
            'Email' => $validated['email'],
            'MotDePasseHash' => Hash::make($validated['password']),
            'Role' => $role,
        ]);

        $utilisateur->profil()->create([
            'Prenom' => $validated['firstName'],
            'Nom' => $validated['lastName'],
        ]);

        Auth::login($utilisateur);

        return redirect('/')->with('Bonjour', 'Bienvenue ! Votre compte a été créé avec succès.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/')->with('Au revoir', 'Vous avez été déconnecté avec succès. À bientôt !');
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['email' => 'Erreur lors de la connexion avec Google.']);
        }

        $utilisateur = Utilisateur::where('GoogleId', $googleUser->getId())->first();

        if (!$utilisateur) {
            $utilisateur = Utilisateur::where('Email', $googleUser->getEmail())->first();

            if ($utilisateur) {
                $utilisateur->update(['GoogleId' => $googleUser->getId()]);
            } else {
                $utilisateur = Utilisateur::create([
                    'Email' => $googleUser->getEmail(),
                    'GoogleId' => $googleUser->getId(),
                    'Role' => 'utilisateur',
                    'MotDePasseHash' => null
                ]);
                
                $nameParts = explode(' ', $googleUser->getName(), 2);
                $firstName = $nameParts[0] ?? '';
                $lastName = $nameParts[1] ?? '';

                $utilisateur->profil()->create([
                     'Prenom' => $firstName,
                     'Nom' => $lastName,
                     'PhotoProfil' => $googleUser->getAvatar(),
                ]);
            }
        }

        Auth::login($utilisateur);
        return redirect('/')->with('success', 'Connexion via Google réussie !');
    }

    public function updateAttribute(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        $validated = $request->validate([
            'table' => ['required', 'string', 'in:users,profiles,social'],
            'attribute' => ['required', 'string'],
            'value' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = Auth::user();
        
        try {
            if ($validated['table'] === 'users') {
                $allowedUserColumns = ['Email'];
                if (!in_array($validated['attribute'], $allowedUserColumns)) {
                    return response()->json(['message' => 'Interdit'], 403);
                }

                $user->{$validated['attribute']} = $validated['value'];
                $user->save();
            } 
            else if ($validated['table'] === 'profiles') {
                $allowedProfileColumns = ['Prenom', 'Nom', 'Metier', 'NumeroTelephone', 'Adresse', 'Biographie', 'PhotoProfil', 'EmailContact'];
                if (!in_array($validated['attribute'], $allowedProfileColumns)) {
                    return response()->json(['message' => 'Interdit'], 403);
                }

                ProfilUtilisateur::updateOrCreate(
                    ['IdUtilisateur' => $user->IdUtilisateur],
                    [$validated['attribute'] => $validated['value']]
                );
            }
            else if ($validated['table'] === 'social') {
                $profil = $user->profil;
                if (!$profil) {
                    $profil = ProfilUtilisateur::create(['IdUtilisateur' => $user->IdUtilisateur]);
                }
    
                $profil->reseauxSociaux()->updateOrCreate(
                    ['Nom' => $validated['attribute']],
                    ['Lien' => $validated['value']]
                );
            }

            return back()->with('success', 'Mise à jour effectuée');

        } catch (\Exception $e) {
            \Log::error('Erreur UpdateAttribute: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur serveur',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
