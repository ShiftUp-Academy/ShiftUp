<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UtilisateurController;
use App\Http\Controllers\ProgrammeController;
use App\Http\Controllers\CoachingController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\LiveController;
use App\Http\Controllers\TemoignageController;
use App\Http\Controllers\GeminiChatController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ContactController;
use Illuminate\Http\Request;

Route::post('/locale', function (Request $request) {
    $request->validate(['locale' => 'required|in:fr,en,mg']);
    session()->put('locale', $request->locale);
    return back();
})->name('locale.change');

Route::get('/', [ProgrammeController::class, 'home']);
Route::post('/ai/chat', [GeminiChatController::class, 'chat'])->name('ai.chat');
Route::get('/live', [LiveController::class, 'index'])->name('live.index');

Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    
    Route::get('/panier', [CommandeController::class, 'viewPanier'])->name('panier.index');
    Route::post('/panier/ajouter', [CommandeController::class, 'ajouterAuPanier'])->name('panier.add');
    Route::delete('/panier/{id}', [CommandeController::class, 'supprimerDuPanier'])->name('panier.remove');

    Route::post('/payment/mvola', [\App\Http\Controllers\PaymentController::class, 'initMvolaPayment'])->name('payment.mvola');
    Route::post('/payment/orange-money', [\App\Http\Controllers\PaymentController::class, 'initOrangeMoneyPayment'])->name('payment.orange_money');

    Route::get('/payment/mock-success', [\App\Http\Controllers\PaymentController::class, 'mockSuccess'])->name('payment.mock_success');

    Route::get('/reservations', [\App\Http\Controllers\ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/mes-reussites', [\App\Http\Controllers\ReussiteController::class, 'userReussites'])->name('user.reussites');
});

Route::get('/menu', function () {
    return Inertia::render('Menus');
});

Route::get('/organisme', function () {
    return Inertia::render('Organisme');
});

Route::get('/comment-ca-fonctionne', function () {
    return Inertia::render('CommentCaFonctionne');
});

Route::get('/politique-de-confidentialite', function () {
    return Inertia::render('PolitiqueDeConfidentialite');
})->name('politique.confidentialite');


Route::get('/toutcategorie', [ProgrammeController::class, 'publicList']);

Route::get('/consultations', [ConsultationController::class, 'index'])->name('consultations.index');
Route::get('/coaching', [CoachingController::class, 'index'])->name('coachings.index');
Route::post('/coaching/reserve', [CoachingController::class, 'storeReservation'])->name('coachings.reserve')->middleware('auth');


Route::get('/login', function () {
    return Inertia::render('Login');
})->name('login');

Route::post('/login', [UtilisateurController::class, 'login'])->name('login.submit');
Route::post('/inscription', [UtilisateurController::class, 'inscription'])->name('inscription.submit');
Route::post('/send-otp', [UtilisateurController::class, 'sendOtp'])->name('otp.send');
Route::post('/forgot-password/send-otp', [UtilisateurController::class, 'sendForgotPasswordOtp'])->name('password.otp.send');
Route::post('/password/reset', [UtilisateurController::class, 'resetPassword'])->name('password.reset.submit');
Route::post('/logout', [UtilisateurController::class, 'logout'])->name('logout');

Route::get('/auth/google/redirect', [UtilisateurController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/auth/google/callback', [UtilisateurController::class, 'handleGoogleCallback'])->name('google.callback');
Route::get('/api/public-profile/{id}', [UtilisateurController::class, 'publicProfile'])->name('api.public-profile');
Route::post('/user/update-attribute', [UtilisateurController::class, 'updateAttribute'])->name('user.update-attribute');

Route::get('/contact', function () {
    return Inertia::render('Contact');
});
Route::post('/contact/send', [ContactController::class, 'send'])->name('contact.send');

Route::get('/programmes', [ProgrammeController::class, 'programmesPublic']);
Route::get('/articles-conseils', [ProgrammeController::class, 'articlesConseils'])->name('articles.index');
Route::get('/offres', [\App\Http\Controllers\OffreController::class, 'publicIndex'])->name('offres.public');
Route::get('/temoignages', [TemoignageController::class, 'index'])->name('temoignages.index');
Route::post('/temoignages', [TemoignageController::class, 'store'])->name('temoignages.store')->middleware('auth');

Route::get('/programmes/{id}', [ProgrammeController::class, 'show'])->name('programmes.show');
Route::get('/seminaires/{id}', [ProgrammeController::class, 'showSeminaire'])->name('seminaires.show');
Route::get('/lecons/{id}/content', [ProgrammeController::class, 'showLessonContent'])->name('lecons.content');
Route::post('/progress/mark', [ProgrammeController::class, 'markProgress'])->name('progress.mark');
Route::post('/lecons/record-opening', [ProgrammeController::class, 'recordOpening'])->name('lecons.record-opening');
Route::post('/consultations', [ConsultationController::class, 'store'])->name('consultations.store');

Route::prefix('admin')->middleware(['auth', 'moderator'])->group(function () {
    Route::get('/programmes', [ProgrammeController::class, 'index'])->name('admin.programmes');
    Route::post('/programmes/insertion', [ProgrammeController::class, 'InsertionProgramme'])->name('admin.programmes.insertion');
    Route::post('/programmes/{id}/update', [ProgrammeController::class, 'majProgramme'])->name('admin.programmes.update');
    Route::delete('/programmes/{id}', [ProgrammeController::class, 'destroy'])->name('admin.programmes.delete');
    Route::post('/programmes/{id}/duplicate', [ProgrammeController::class, 'duplicate'])->name('admin.programmes.duplicate');
    Route::post('/lecons/insertion', [ProgrammeController::class, 'insertionLecon'])->name('admin.lecons.insertion');
    Route::post('/lecons/{id}/maj', [ProgrammeController::class, 'miseAJourLecon'])->name('admin.lecons.maj');
    Route::delete('/lecons/{id}', [ProgrammeController::class, 'supprimerLecon'])->name('admin.lecons.suppression');
    Route::post('/lecons/duplication/{id}', [ProgrammeController::class, 'dupliquerLecon'])->name('admin.lecons.duplication');
    
    Route::post('/etapes/insertion', [ProgrammeController::class, 'insertionEtape'])->name('admin.etapes.insertion');
    Route::post('/etapes/{id}/maj', [ProgrammeController::class, 'majEtape'])->name('admin.etapes.maj');
    Route::delete('/etapes/{id}', [ProgrammeController::class, 'supprimerEtape'])->name('admin.etapes.suppression');
    Route::post('/themes/insertion', [ProgrammeController::class, 'insertionTheme'])->name('admin.themes.store');
    Route::post('/themes/{id}/maj', [ProgrammeController::class, 'majTheme'])->name('admin.themes.update');
    Route::delete('/themes/{id}', [ProgrammeController::class, 'supprimerTheme'])->name('admin.themes.delete');
    Route::get('/programmes/trash/data', [ProgrammeController::class, 'trashData'])->name('admin.programmes.trash.data');
    Route::post('/programmes/restore/{type}/{id}', [ProgrammeController::class, 'restore'])->name('admin.programmes.restore');
    Route::get('/consultations', function () {
        $consultations = \App\Models\Consultation::with(['utilisateur.profil', 'lecon.theme.programme', 'categorie'])
            ->orderBy('DateCreation', 'desc')
            ->get();
            
        $reponseConsultations = \App\Models\ReponseConsultation::with(['categorie', 'questions.lecon.theme.programme'])
            ->withCount('questions')
            ->orderBy('DateCreation', 'desc')
            ->get();

        $categories = \App\Models\Categorie::all();
        
        return Inertia::render('PagesAdmin/AdminConsultations', [
            'consultations' => $consultations,
            'reponseConsultations' => $reponseConsultations,
            'categories' => $categories
        ]);
    })->name('admin.consultations');
    Route::post('/consultations/store-response', [ConsultationController::class, 'storeResponse'])->name('admin.consultations.storeResponse');
    Route::post('/consultations/update-response/{id}', [ConsultationController::class, 'updateResponse'])->name('admin.consultations.updateResponse');
    Route::delete('/consultations/{id}', [ConsultationController::class, 'destroy'])->name('admin.consultations.delete');
    Route::delete('/consultations/reponse/{id}', [ConsultationController::class, 'destroyArchive'])->name('admin.consultations.deleteArchive');
    Route::get('/lives', [\App\Http\Controllers\LiveController::class, 'adminIndex'])->name('admin.lives');
    Route::post('/lives', [\App\Http\Controllers\LiveController::class, 'store'])->name('admin.lives.store');
    Route::post('/lives/{id}', [\App\Http\Controllers\LiveController::class, 'update'])->name('admin.lives.update');
    Route::delete('/lives/{id}', [\App\Http\Controllers\LiveController::class, 'destroy'])->name('admin.lives.delete');
    Route::get('/coachings', [\App\Http\Controllers\CoachingController::class, 'adminIndex'])->name('admin.coachings');
    Route::post('/coachings/types', [\App\Http\Controllers\CoachingController::class, 'storeType'])->name('admin.coachings.types.store');
    Route::post('/coachings/types/{id}', [\App\Http\Controllers\CoachingController::class, 'updateType'])->name('admin.coachings.types.update');
    Route::post('/coachings/types/{id}/status', [CoachingController::class, 'updateStatus'])->name('admin.coachings.types.status');
    Route::delete('/coachings/types/{id}', [CoachingController::class, 'destroyType'])->name('admin.coachings.types.delete');

    Route::post('/coachings/reservations/{id}/update', [\App\Http\Controllers\CoachingController::class, 'updateReservation'])->name('admin.coachings.reservations.update');
    Route::post('/coachings/availabilities', [\App\Http\Controllers\CoachingController::class, 'storeAvailabilities'])->name('admin.coachings.availabilities.store');
    Route::delete('/coachings/availabilities/{id}', [\App\Http\Controllers\CoachingController::class, 'destroyAvailability'])->name('admin.coachings.availabilities.delete');
    Route::post('/coachings/google-meet', [\App\Http\Controllers\CoachingController::class, 'storeLienGoogle'])->name('admin.coachings.google-meet.store');
    Route::get('/offres', [\App\Http\Controllers\OffreController::class, 'index'])->name('admin.offres');
    Route::post('/offres', [\App\Http\Controllers\OffreController::class, 'store'])->name('admin.offres.store');
    Route::post('/offres/{id}/update', [\App\Http\Controllers\OffreController::class, 'update'])->name('admin.offres.update');
    Route::post('/offres/{id}/status', [\App\Http\Controllers\OffreController::class, 'toggleStatus'])->name('admin.offres.status');
    Route::delete('/offres/{id}', [\App\Http\Controllers\OffreController::class, 'destroy'])->name('admin.offres.delete');
    Route::get('/utilisateurs', [UtilisateurController::class, 'adminIndex'])->name('admin.utilisateurs');
    Route::get('/temoignages', function () {
        $temoignages = \App\Models\Temoignage::with(['utilisateur.profil'])
            ->orderBy('DateCreation', 'desc')
            ->get();
        $users = \App\Models\Utilisateur::with('profil')->get();
        return Inertia::render('PagesAdmin/AdminTemoignages', [
            'temoignages' => $temoignages,
            'users' => $users
        ]);
    })->name('admin.temoignages');
    Route::post('/temoignages/{id}/update', [TemoignageController::class, 'update'])->name('admin.temoignages.update');
    Route::delete('/temoignages/{id}', [TemoignageController::class, 'destroy'])->name('admin.temoignages.delete');
    Route::get('/categories', [\App\Http\Controllers\CategorieController::class, 'index'])->name('admin.categories');
    Route::post('/categories', [\App\Http\Controllers\CategorieController::class, 'store'])->name('admin.categories.store');
    Route::post('/categories/{id}', [\App\Http\Controllers\CategorieController::class, 'update'])->name('admin.categories.update');
    Route::delete('/categories/{id}', [\App\Http\Controllers\CategorieController::class, 'destroy'])->name('admin.categories.delete');
    Route::post('/newsletter/send', [UtilisateurController::class, 'sendNewsletter'])->name('admin.newsletter.send');
    Route::post('/utilisateurs/assign-offer', [UtilisateurController::class, 'assignOffer'])->name('admin.utilisateurs.assign-offer');
    Route::get('/utilisateurs/search', [UtilisateurController::class, 'searchUsers'])->name('admin.utilisateurs.search');
    Route::post('/utilisateurs/update-moderator', [UtilisateurController::class, 'updateModerator'])->name('admin.utilisateurs.update-moderator');
    Route::post('/admin-verify-password', [UtilisateurController::class, 'verifyAdminPassword'])->name('admin.verify-password');
    Route::post('/admin-update-profile', [UtilisateurController::class, 'updateAdminProfile'])->name('admin.update-profile');
    Route::get('/utilisateurs/{id}/details', [UtilisateurController::class, 'getUserDetails'])->name('admin.utilisateurs.details');
    Route::delete('/newsletter/subscription/{id}', [UtilisateurController::class, 'deleteNewsletterSubscription'])->name('admin.newsletter.delete');

    // Réussites
    Route::get('/reussites', [\App\Http\Controllers\ReussiteController::class, 'index'])->name('admin.reussites');
    Route::post('/reussites', [\App\Http\Controllers\ReussiteController::class, 'store'])->name('admin.reussites.store');
    Route::post('/reussites/{id}/update', [\App\Http\Controllers\ReussiteController::class, 'update'])->name('admin.reussites.update');
    Route::delete('/reussites/{id}', [\App\Http\Controllers\ReussiteController::class, 'destroy'])->name('admin.reussites.delete');

    // Validations (Submissions d'étapes)
    Route::get('/programmes/submissions', [ProgrammeController::class, 'getPendingSubmissions'])->name('admin.programmes.submissions');
    Route::post('/programmes/submissions/{id}/validate', [ProgrammeController::class, 'validateSubmission'])->name('admin.programmes.validate');
});

Route::middleware('auth')->group(function () {
    Route::post('/steps/{id}/submit', [ProgrammeController::class, 'submitStepResponse'])->name('steps.submit');
});

Route::post('/newsletter/subscribe', [UtilisateurController::class, 'subscribeNewsletter'])->name('newsletter.subscribe');



