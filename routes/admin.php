<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\AdmissionCycleController;
use App\Http\Controllers\Admin\TermController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified', 'school.context', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', function () {
            return Inertia::render('admin/Dashboard');
        })->name('dashboard');

        Route::get(
            '/staff',
            [\App\Http\Controllers\Admin\SchoolStaffController::class, 'index']
        )->name('staff.index');

        Route::get(
            '/staff/invite',
            [\App\Http\Controllers\Admin\SchoolInvitationController::class, 'invite']
        )->name('staff.invite');

        Route::post(
            '/staff/invite',
            [\App\Http\Controllers\Admin\SchoolInvitationController::class, 'store']
        )->name('staff.store');

        Route::prefix('settings')
            ->name('settings.')
            ->group(function () {
                // Permissions for settings modules
                // Consider gating each group with permission middleware in future
                Route::get(
                    '/academic-years',
                    [AcademicYearController::class, 'index']
                )->name('academic-year.index');

                Route::post(
                    '/academic-years',
                    [AcademicYearController::class, 'store']
                )->name('academic-year.store');

                Route::get(
                    '/admission-cycles',
                    [AdmissionCycleController::class, 'index']
                )->name('admission-cycles.index');

                Route::post(
                    '/admission-cycles',
                    [AdmissionCycleController::class, 'store']
                )->name('admission-cycles.store');

                Route::get(
                    '/terms',
                    [TermController::class, 'index']
                )->name('terms.index');

                Route::post(
                    '/terms',
                    [TermController::class, 'store']
                )->name('terms.store');

                Route::put(
                    '/terms/{term}',
                    [TermController::class, 'update']
                )->name('terms.update');

                Route::delete(
                    '/terms/{term}',
                    [TermController::class, 'destroy']
                )->name('terms.destroy');

                Route::post(
                    '/terms/generate-defaults',
                    [TermController::class, 'generateDefaults']
                )->name('terms.generate-defaults');
            });


    });
