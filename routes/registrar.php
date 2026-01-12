<?php

use App\Http\Controllers\Registrar\AdmissionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified', 'school.context', 'role:registrar'])
    ->prefix('registrar')
    ->name('registrar.')
    ->group(function () {

        Route::get('/dashboard', function () {
            return Inertia::render('registrar/Dashboard');
        })->name('dashboard');

        Route::resource('admissions', AdmissionController::class);

    });
