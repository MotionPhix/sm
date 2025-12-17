<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'super_admin'])
    ->prefix('super-user')
    ->name('super.')
    ->group(function () {
        Route::get('/schools', SchoolsController::class);
    });
