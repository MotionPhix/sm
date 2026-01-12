<?php


use App\Http\Controllers\Auth\AdminRegistrationController;
use App\Http\Controllers\Auth\InvitationAcceptanceController;
use App\Http\Controllers\Onboarding\SchoolSetupController;
use App\Http\Controllers\Onboarding\SchoolSelectionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard.redirect');
    }

    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get(
    '/invitations/{token}',
    [InvitationAcceptanceController::class, 'show']
)->name('invitations.show');

Route::post(
    '/invitations/{token}',
    [InvitationAcceptanceController::class, 'store']
)->name('invitations.store');

Route::middleware('guest')->group(function () {
    Route::get(
        '/admin-register',
        [AdminRegistrationController::class, 'create']
    )->name('admin.register.create');

    Route::post(
        '/admin-register',
        [AdminRegistrationController::class, 'store']
    )->name('admin.register.store');
});

Route::middleware(['auth'])->group(function () {
    Route::get(
        '/onboarding/school-setup',
        [SchoolSetupController::class, 'create']
    )->name('onboarding.school-setup.create');

    Route::post(
        '/onboarding/school-setup',
        [SchoolSetupController::class, 'store']
    )->name('onboarding.school-setup.store');

    Route::get(
        '/select-school',
        [SchoolSelectionController::class, 'index']
    )->name('schools.select.index');

    Route::post(
        '/select-school',
        [SchoolSelectionController::class, 'store']
    )->name('schools.select.store');

    Route::get(
        '/dashboard',
        function () {
            return Inertia::render('dashboard/Index');
        }
    )->name('dashboard');

    Route::get('/dashboard-redirect', function () {
        return app(\Laravel\Fortify\Contracts\LoginResponse::class)
            ->toResponse(request());
    })->name('dashboard.redirect');
});

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
require __DIR__.'/teacher.php';
require __DIR__.'/parent.php';
require __DIR__.'/student.php';
require __DIR__.'/registrar.php';
// require __DIR__.'/super-admin.php';
