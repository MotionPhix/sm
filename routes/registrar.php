<?php

use App\Http\Controllers\Registrar\AdmissionController;
use App\Http\Controllers\Registrar\StudentController;
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

        Route::resource('students', StudentController::class);

        // Additional student routes
        Route::prefix('students/{student}')->group(function () {
            Route::get('/enroll', [StudentController::class, 'enrollForm'])
                ->name('students.enroll.form');
            Route::post('/enroll', [StudentController::class, 'enroll'])
                ->name('students.enroll');
            Route::get('/transfer', [StudentController::class, 'transferForm'])
                ->name('students.transfer.form');
            Route::post('/transfer', [StudentController::class, 'transfer'])
                ->name('students.transfer');
            Route::post('/withdraw', [StudentController::class, 'withdraw'])
                ->name('students.withdraw');
        });

        // API endpoint for available class/streams
        Route::get('/api/class-streams', [StudentController::class, 'getAvailableClassStreams'])
            ->name('api.class-streams');

    });
