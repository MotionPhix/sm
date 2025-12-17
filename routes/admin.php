<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'school', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', AdminDashboardController::class)
            ->name('dashboard');
    });
