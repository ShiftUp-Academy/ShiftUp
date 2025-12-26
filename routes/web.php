<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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