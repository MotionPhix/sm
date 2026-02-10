<?php

use App\Http\Controllers\Bursar\DashboardController;
use App\Http\Controllers\Bursar\InvoiceController;
use App\Http\Controllers\Bursar\PaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'school.context', 'role:bursar'])
    ->prefix('bursar')
    ->name('bursar.')
    ->group(function () {

        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        // Invoice routes
        Route::resource('invoices', InvoiceController::class)->except(['edit', 'update', 'destroy']);

        // Additional invoice routes
        Route::post('/invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])
            ->name('invoices.cancel');

        // Payment routes
        Route::post('/invoices/{invoice}/payments', [PaymentController::class, 'store'])
            ->name('invoices.payments.store');

        Route::get('/payments/{payment}', [PaymentController::class, 'show'])
            ->name('payments.show');

        Route::get('/students/{student}/payments', [PaymentController::class, 'history'])
            ->name('students.payments.history');

    });
