<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UtilisateurController;

Route::get('/', function () {
    return Inertia::render('Home');
});

Route::get('/menu', function () {
    return Inertia::render('Menus');
});

Route::get('/organisme', function () {
    return Inertia::render('Organisme');
});

Route::get('/toutcategorie', function () {
    return Inertia::render('ToutCategorie');
});

Route::get('/login', function () {
    return Inertia::render('Login');
})->name('login');

Route::post('/login', [UtilisateurController::class, 'login'])->name('login.submit');
Route::post('/inscription', [UtilisateurController::class, 'inscription'])->name('inscription.submit');
Route::post('/logout', [UtilisateurController::class, 'logout'])->name('logout');

Route::get('/auth/google/redirect', [UtilisateurController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/auth/google/callback', [UtilisateurController::class, 'handleGoogleCallback'])->name('google.callback');
Route::post('/user/update-attribute', [UtilisateurController::class, 'updateAttribute'])->name('user.update-attribute');

Route::get('/contact', function () {
    return Inertia::render('Contact');
});

Route::get('/programmes', function () {
    return Inertia::render('Programmes');
});