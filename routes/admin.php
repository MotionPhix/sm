<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\AdmissionCycleController;
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
            });


    });
