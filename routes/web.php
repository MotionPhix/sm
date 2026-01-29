<?php

use App\Http\Controllers\Auth\AdminRegistrationController;
use App\Http\Controllers\Auth\InvitationAcceptanceController;
use App\Http\Controllers\Onboarding\SchoolSelectionController;
use App\Http\Controllers\Onboarding\SchoolSetupController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group.
|
*/

// Public landing page
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('welcome');

// Admin registration (school owners only)
Route::middleware('guest')->group(function () {
    Route::get('/admin/register', [AdminRegistrationController::class, 'create'])
        ->name('admin.register.create');
    Route::post('/admin/register', [AdminRegistrationController::class, 'store'])
        ->name('admin.register.store');
});

// Invitation acceptance (guests and existing users)
Route::get('/invitations/{token}', [InvitationAcceptanceController::class, 'show'])
    ->name('invitations.accept');
Route::post('/invitations/{token}', [InvitationAcceptanceController::class, 'store'])
    ->name('invitations.store');

// Onboarding routes (authenticated users without a school)
Route::middleware('auth')->group(function () {
    // Onboarding data endpoints
    Route::get('/api/onboarding/school-types', [\App\Http\Controllers\Onboarding\OnboardingDataController::class, 'schoolTypes'])
        ->name('api.onboarding.school-types');
    Route::get('/api/onboarding/countries', [\App\Http\Controllers\Onboarding\OnboardingDataController::class, 'countries'])
        ->name('api.onboarding.countries');
    Route::get('/api/onboarding/regions', [\App\Http\Controllers\Onboarding\OnboardingDataController::class, 'regions'])
        ->name('api.onboarding.regions');
    Route::get('/api/onboarding/districts', [\App\Http\Controllers\Onboarding\OnboardingDataController::class, 'districts'])
        ->name('api.onboarding.districts');

    // School setup flow
    Route::get('/onboarding/school-setup', [SchoolSetupController::class, 'create'])
        ->name('onboarding.school-setup.create');
    Route::post('/onboarding/school-setup', [SchoolSetupController::class, 'store'])
        ->name('onboarding.school-setup.store');

    // Classes setup
    Route::get('/onboarding/classes', [\App\Http\Controllers\Onboarding\ClassesSetupController::class, 'create'])
        ->name('onboarding.classes.create');
    Route::post('/onboarding/classes', [\App\Http\Controllers\Onboarding\ClassesSetupController::class, 'store'])
        ->name('onboarding.classes.store');

    // Streams setup
    Route::get('/onboarding/streams', [\App\Http\Controllers\Onboarding\StreamsSetupController::class, 'create'])
        ->name('onboarding.streams.create');
    Route::post('/onboarding/streams', [\App\Http\Controllers\Onboarding\StreamsSetupController::class, 'store'])
        ->name('onboarding.streams.store');

    // Subjects setup
    Route::get('/onboarding/subjects', [\App\Http\Controllers\Onboarding\SubjectsSetupController::class, 'create'])
        ->name('onboarding.subjects.create');
    Route::post('/onboarding/subjects', [\App\Http\Controllers\Onboarding\SubjectsSetupController::class, 'store'])
        ->name('onboarding.subjects.store');

    // School selection for multi-school users
    Route::get('/schools/select', [SchoolSelectionController::class, 'index'])
        ->name('schools.select.index');
    Route::post('/schools/select', [SchoolSelectionController::class, 'store'])
        ->name('schools.select.store');
});

// Dashboard redirect - routes to role-specific dashboard
Route::get('/dashboard', function () {
    $user = auth()->user();
    $role = $user->roleForActiveSchool()?->name;

    $routeMap = [
        'admin'        => 'admin.dashboard',
        'head_teacher' => 'teacher.dashboard',
        'teacher'      => 'teacher.dashboard',
        'registrar'    => 'registrar.dashboard',
        'bursar'       => 'bursar.dashboard',
        'accountant'   => 'accountant.dashboard',
        'parent'       => 'parent.dashboard',
        'student'      => 'student.dashboard',
    ];

    $target = $routeMap[$role] ?? 'admin.dashboard';

    return Route::has($target)
        ? to_route($target)
        : Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Alias for dashboard.redirect (used by invitation acceptance)
Route::get('/dashboard/redirect', function () {
    return to_route('dashboard');
})->middleware('auth')->name('dashboard.redirect');

// API routes for frontend components
Route::middleware(['auth', 'api'])->group(function () {
    Route::get('/api/classes/{id}', function ($id) {
        $school = auth()->user()->activeSchool();
        $class = \App\Models\SchoolClass::where('school_id', $school->id)->findOrFail($id);
        return response()->json($class);
    });

    Route::get('/api/streams/{id}', function ($id) {
        $school = auth()->user()->activeSchool();
        $stream = \App\Models\Stream::where('school_id', $school->id)->findOrFail($id);
        return response()->json($stream);
    });
});

// Tenant-specific routes (require auth + school context)
Route::middleware(['auth', 'school.context'])->group(function () {
    require __DIR__.'/settings.php';
    require __DIR__.'/teacher.php';
    require __DIR__.'/registrar.php';
    require __DIR__.'/bursar.php';
    require __DIR__.'/accountant.php';
    require __DIR__.'/student.php';
    require __DIR__.'/parent.php';
});

// Admin routes (has its own complete middleware stack)
require __DIR__.'/admin.php';