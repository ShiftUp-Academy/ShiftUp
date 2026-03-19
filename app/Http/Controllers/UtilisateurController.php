<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use App\Models\ProfilUtilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

use Illuminate\Support\Facades\Mail;
use App\Mail\NewsletterMail;
use App\Models\NewsletterSubscription;
use App\Models\Offre;
use App\Models\OffreUtilisateur;
use Illuminate\Support\Facades\Cache;
use App\Mail\VerificationCodeMail;
use App\Mail\ForgotPasswordMail;

class UtilisateurController extends Controller
{
    public function publicProfile($id)
    {
        $user = Utilisateur::with([
            'profil.reseauxSociaux',
            'temoignages' => function($q) {
                $q->where('Statut', 'Publié')->orderBy('DateCreation', 'desc');
            }
        ])->findOrFail($id);

        $reussites = \App\Models\Reussite::whereHas('utilisateurs', function($q) use ($id) {
            $q->where('user_id', $id);
        })->get();

        return response()->json([
            'profil' => $user->profil,
            'temoignages' => $user->temoignages,
            'reussites' => $reussites,
            'email' => $user->Email // For some public identification
        ]);
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            $email = trim($credentials['email']);
            $password = $credentials['password'];

            $user = Utilisateur::where('Email', $email)->first();

            if ($user && Hash::check($password, $user->MotDePasseHash)) {
                $user->update(['DerniereConnexion' => now()]);
                Auth::login($user, $request->has('remember'));
                $request->session()->regenerate();

                if ($user->Role === 'admin' || $user->Role === 'moderateur') {
                    $greeting = ($email === 'monsieuradmin@gmail.com') ? 'Bienvenue Monsieur l\'Administrateur !' : 'Bienvenue dans votre espace d\'administration !';
                    
                    if ($user->Role === 'moderateur') {
                        $permissions = $user->PermissionsModerateur ?? [];
                        // Mapping permission key to relative URL
                        $mapping = [
                            'programmes' => '/admin/programmes',
                            'consultations' => '/admin/consultations',
                            'lives' => '/admin/lives',
                            'coachings' => '/admin/coachings',
                            'offres' => '/admin/offres',
                            'utilisateurs' => '/admin/utilisateurs',
                            'temoignages' => '/admin/temoignages',
                        ];
                        
                        foreach ($mapping as $key => $targetUrl) {
                            if (in_array($key, $permissions)) {
                                return redirect()->intended($targetUrl)->with('Bonjour', $greeting);
                            }
                        }
                        
                        return redirect()->intended('/')->with('error', 'Compte modérateur sans permissions actives.');
                    }

                    return redirect()->intended('/admin/programmes')->with('Bonjour', $greeting);
                }

                return redirect()->intended('/')->with('Bonjour', 'Connexion réussie. Ravi de vous revoir!');
            }

            return back()->withErrors([
                'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
            ])->onlyInput('email');

        } catch (\Exception $e) {
            \Log::error('ERREUR LOGIN: ' . $e->getMessage());
            return back()->withErrors([
                'email' => 'DÉTAIL ERREUR SERVEUR : ' . $e->getMessage() . ' à la ligne ' . $e->getLine(),
            ])->onlyInput('email');
        }
    }

    public function sendOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:Utilisateurs,Email'],
        ]);

        $email = $validated['email'];
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT); // Generate 6-digit code

        Cache::put('otp_' . $email, $code, now()->addMinutes(5)); // Store for 5 minutes

        Mail::to($email)->send(new VerificationCodeMail($code));

        return response()->json(['message' => 'Un code de vérification a été envoyé à votre adresse e-mail.']);
    }

    public function sendForgotPasswordOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'exists:Utilisateurs,Email'],
        ]);

        $email = $validated['email'];
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put('password_otp_' . $email, $code, now()->addMinutes(10));

        Mail::to($email)->send(new ForgotPasswordMail($code));

        return response()->json(['message' => 'Un code de réinitialisation a été envoyé.']);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'exists:Utilisateurs,Email'],
            'code' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $cachedCode = Cache::get('password_otp_' . $validated['email']);

        if (!$cachedCode || $cachedCode != $validated['code']) {
            return back()->withErrors(['code' => 'Le code de vérification est invalide ou a expiré.']);
        }

        Cache::forget('password_otp_' . $validated['email']);

        $user = Utilisateur::where('Email', $validated['email'])->first();
        $user->update([
            'MotDePasseHash' => Hash::make($validated['password'])
        ]);

        return redirect('/')->with('Bonjour', 'Votre mot de passe a été réinitialisé avec succès.');
    }

    public function inscription(Request $request)
    {
        $validated = $request->validate([
            'firstName' => ['required', 'string', 'max:100'],
            'lastName' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:Utilisateurs,Email'],
            'password' => ['required', 'string', 'min:8'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $cachedCode = Cache::get('otp_' . $validated['email']);

        if (!$cachedCode || $cachedCode != $validated['code']) {
            return back()->withErrors(['code' => 'Le code de vérification est invalide ou a expiré.']);
        }

        Cache::forget('otp_' . $validated['email']);

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
        $request->session()->regenerate();

        return redirect('/')->with('Bonjour', 'Bienvenue ! Votre compte a été créé avec succès.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return \Inertia\Inertia::location('/');
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

        $utilisateur->update(['DerniereConnexion' => now()]);
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
            ], 500);
        }
    }

    public function adminIndex()
    {
        $utilisateurs = Utilisateur::with(['profil', 'commandes.items.programme', 'consultations'])
            ->withCount(['commandes', 'consultations', 'questionsLibres', 'reponsesExercices'])
            ->get()
            ->map(function ($user) {
                $items = $user->commandes->flatMap->items;
                // Determine if they bought a regular program
                $user->hasBoughtProgram = $items->whereNotNull('IdProgrammeFormation')
                    ->filter(fn($i) => $i->programme && $i->programme->Type !== 'Seminaire')
                    ->isNotEmpty();
                
                // Determine if they bought a seminar
                $user->hasReservedSeminar = $items->whereNotNull('IdProgrammeFormation')
                    ->filter(fn($i) => $i->programme && $i->programme->Type === 'Seminaire')
                    ->isNotEmpty();

                // Determine if they bought an offer
                $user->hasBoughtOffer = $items->whereNotNull('IdOffre')->isNotEmpty();
                
                // Check coaching
                $user->hasReservedCoaching = \App\Models\ReservationCoaching::where('IdUtilisateur', $user->IdUtilisateur)->exists();
                
                $user->isNewsletterOnly = false;
                $user->PermissionsModerateur = $user->PermissionsModerateur ?? [];
                
                return $user;
            })->toArray();

        $newsletterSubscribers = NewsletterSubscription::all()->map(function($sub) {
            return [
                'IdUtilisateur' => 'sub_' . $sub->IdSubscription,
                'Email' => $sub->Email,
                'DateCreation' => $sub->DateSouscription,
                'Role' => 'utilisateur',
                'isNewsletterOnly' => true,
                'Newsletter' => true,
                'profil' => null,
                'commandes_count' => 0,
                'consultations_count' => 0,
                'reponses_exercices_count' => 0,
                'hasBoughtProgram' => false,
                'hasBoughtOffer' => false,
                'hasReservedCoaching' => false,
            ];
        })->toArray();

        // Merge both lists
        $allMembers = array_merge($utilisateurs, $newsletterSubscribers);

        $offres = Offre::where('Statut', 'Publié')->get();

        return \Inertia\Inertia::render('PagesAdmin/AdminUtilisateurs', [
            'utilisateurs' => $allMembers,
            'offres' => $offres
        ]);
    }

    public function deleteNewsletterSubscription($id)
    {
        // $id comes as 'sub_X'
        $realId = str_replace('sub_', '', $id);
        NewsletterSubscription::where('IdSubscription', $realId)->delete();

        return back()->with('success', 'Inscription supprimée définitivement.');
    }

    public function subscribeNewsletter(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $email = $request->email;

        // Check if user account exists
        $user = Utilisateur::where('Email', $email)->first();
        if ($user) {
            $user->update(['Newsletter' => true]);
        } else {
            // Add to separate table
            NewsletterSubscription::firstOrCreate(['Email' => $email]);
        }

        return back()->with('success', 'Merci pour votre inscription à notre newsletter !');
    }

    public function sendNewsletter(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $subject = $request->subject;
        $content = $request->content;

        // Get all registered users who subscribed
        $userEmails = Utilisateur::where('Newsletter', true)->pluck('Email')->toArray();
        // Get all footer subscribers
        $subscriberEmails = NewsletterSubscription::pluck('Email')->toArray();

        $allEmails = array_unique(array_merge($userEmails, $subscriberEmails));

        foreach ($allEmails as $email) {
            Mail::to($email)->send(new NewsletterMail($subject, $content));
        }

        return back()->with('success', 'Newsletter envoyée avec succès à ' . count($allEmails) . ' destinataires.');
    }

    public function assignOffer(Request $request)
    {
        $request->validate([
            'userIds' => 'required|array',
            'offerId' => 'required|exists:Offres,IdOffre',
        ]);

        foreach ($request->userIds as $userId) {
            // Store in our manual assignment table
            \App\Models\OffreUtilisateur::firstOrCreate([
                'IdUtilisateur' => $userId,
                'IdOffre' => $request->offerId,
            ]);
            
            // Débloquer les réussites
            $user = \App\Models\Utilisateur::find($userId);
            $reussiteService = app(\App\Services\ReussiteService::class);
            $reussiteService->checkAndUnlock($user, 'offre_achetee', ['offer_id' => $request->offerId]);
            
            // Optional: You might want to grant access to individual programs/coachings here 
            // but usually a middleware checks if user has the offer.
        }

        return back()->with('success', 'Offre attribuée avec succès.');
    }

    public function getUserDetails($id)
    {
        // $id can be sub_X if it's a newsletter only
        if (str_starts_with($id, 'sub_')) {
            $realId = str_replace('sub_', '', $id);
            $sub = \App\Models\NewsletterSubscription::find($realId);
            return response()->json([
                'user' => [
                    'Email' => $sub->Email,
                    'isNewsletterOnly' => true,
                    'DateCreation' => $sub->DateSouscription,
                ],
                'purchases' => [],
                'progressions' => [],
                'coachings' => []
            ]);
        }

        $user = Utilisateur::with([
            'profil',
            'commandes.items.programme',
            'commandes.items.offre',
            'progression.programme',
            'progression.lecon',
            'progression.etape',
            'questionsLibres.programme',
            'questionsLibres.lecon',
            'consultations.lecon',
            'consultations.categorie',
        ])->find($id);

        if (!$user) return response()->json(['error' => 'User not found'], 404);

        $coachings = \App\Models\ReservationCoaching::with(['type', 'disponibilite'])
            ->where('IdUtilisateur', $id)
            ->get();
            
        // Manual offer assignments
        $assignedOffers = \App\Models\OffreUtilisateur::with('offre')
            ->where('IdUtilisateur', $id)
            ->get();

        return response()->json([
            'user' => $user,
            'purchases' => $user->commandes,
            'coachings' => $coachings,
            'assignedOffers' => $assignedOffers,
            'progressions' => $user->progression,
            'questions' => $user->consultations,
        ]);
    }

    public function searchUsers(Request $request)
    {
        $q = $request->query('q');
        if (empty($q)) return response()->json([]);

        $users = Utilisateur::with('profil')
            ->where('Email', 'LIKE', "%$q%")
            ->orWhereHas('profil', function($query) use ($q) {
                $query->where('Prenom', 'LIKE', "%$q%")
                    ->orWhere('Nom', 'LIKE', "%$q%");
            })
            ->limit(10)
            ->get();

        return response()->json($users);
    }

    public function updateModerator(Request $request)
    {
        $request->validate([
            'userId' => 'required|exists:Utilisateurs,IdUtilisateur',
            'permissions' => 'nullable|array',
            'isModerator' => 'required|boolean'
        ]);

        $user = Utilisateur::findOrFail($request->userId);
        
        if ($request->isModerator) {
            $user->Role = 'moderateur';
            $user->PermissionsModerateur = $request->permissions;
        } else {
            if ($user->Role === 'moderateur') {
                $user->Role = 'utilisateur';
                $user->PermissionsModerateur = null;
            }
        }
        
        $user->save();

        return back()->with('success', 'Permissions du modérateur mises à jour.');
    }

    public function verifyAdminPassword(Request $request)
    {
        $request->validate(['password' => 'required']);

        if (Hash::check($request->password, Auth::user()->MotDePasseHash)) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Mot de passe incorrect.'], 401);
    }

    public function updateAdminProfile(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'email' => 'required|email|unique:Utilisateurs,Email,' . $user->IdUtilisateur . ',IdUtilisateur',
            'prenom' => 'nullable|string|max:100',
            'nom' => 'nullable|string|max:100',
            'password' => 'nullable|min:8|confirmed',
        ]);

        $user->Email = $request->email;
        if ($request->password) {
            $user->MotDePasseHash = Hash::make($request->password);
        }
        $user->save();

        if ($user->profil) {
            $user->profil->update([
                'Prenom' => $request->prenom,
                'Nom' => $request->nom,
            ]);
        }

        return back()->with('success', 'Profil administrateur mis à jour.');
    }
}
