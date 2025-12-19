<?php


use App\Http\Controllers\Auth\AdminRegistrationController;
use App\Http\Controllers\Onboarding\SchoolSetupController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('dashboard/Index');
})->middleware(['auth', 'verified'])->name('dashboard');

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
    Route::get('/onboarding/school-setup', [SchoolSetupController::class, 'create'])
        ->name('onboarding.school-setup.create');

    Route::post('/onboarding/school-setup', [SchoolSetupController::class, 'store'])
        ->name('onboarding.school-setup.store');
});

require __DIR__.'/settings.php';
