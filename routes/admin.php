<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\AdmissionCycleController;
use App\Http\Controllers\Admin\AssessmentPlanController;
use App\Http\Controllers\Admin\BillingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\FeeItemController;
use App\Http\Controllers\Admin\FeeStructureController;
use App\Http\Controllers\Admin\GradeScaleController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SchoolClassController;
use App\Http\Controllers\Admin\SchoolProfileController;
use App\Http\Controllers\Admin\StreamController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TermController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'school.context', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', DashboardController::class)->name('dashboard');

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

        // Enrollment overview
        Route::get('/enrollment', [EnrollmentController::class, 'index'])
            ->name('enrollment.index');

        // Promotion management
        Route::get('/promotion', [PromotionController::class, 'index'])
            ->name('promotion.index');

        // Reports overview
        Route::get('/reports', [ReportController::class, 'index'])
            ->name('reports.index');

        // Billing / SaaS subscription
        Route::get('/billing', [BillingController::class, 'index'])
            ->name('billing.index');

        Route::prefix('settings')
            ->name('settings.')
            ->group(function () {
                // School Profile
                Route::get('/school-profile', [SchoolProfileController::class, 'show'])
                    ->name('school-profile.show');
                Route::get('/school-profile/edit', [SchoolProfileController::class, 'edit'])
                    ->name('school-profile.edit');
                Route::put('/school-profile', [SchoolProfileController::class, 'update'])
                    ->name('school-profile.update');

                // Academic Years
                Route::get(
                    '/academic-years',
                    [AcademicYearController::class, 'index']
                )->name('academic-year.index');

                Route::post(
                    '/academic-years',
                    [AcademicYearController::class, 'store']
                )->name('academic-year.store');

                // Terms
                Route::get(
                    '/terms',
                    [TermController::class, 'index']
                )->name('terms.index');

                Route::get(
                    '/terms/create',
                    [TermController::class, 'create']
                )->name('terms.create');

                Route::get(
                    '/terms/{term}/edit',
                    [TermController::class, 'edit']
                )->name('terms.edit');

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

                // Classes
                Route::resource('classes', SchoolClassController::class)
                    ->except(['show'])
                    ->parameters(['classes' => 'schoolClass']);

                // Streams
                Route::resource('streams', StreamController::class)
                    ->except(['show']);

                // Subjects
                Route::resource('subjects', SubjectController::class)
                    ->except(['show']);

                // Admission Cycles
                Route::get(
                    '/admission-cycles',
                    [AdmissionCycleController::class, 'index']
                )->name('admission-cycles.index');

                Route::get(
                    '/admission-cycles/create',
                    [AdmissionCycleController::class, 'create']
                )->name('admission-cycles.create');

                Route::get(
                    '/admission-cycles/{admissionCycle}/edit',
                    [AdmissionCycleController::class, 'edit']
                )->name('admission-cycles.edit');

                Route::post(
                    '/admission-cycles',
                    [AdmissionCycleController::class, 'store']
                )->name('admission-cycles.store');

                Route::put(
                    '/admission-cycles/{admissionCycle}',
                    [AdmissionCycleController::class, 'update']
                )->name('admission-cycles.update');

                Route::delete(
                    '/admission-cycles/{admissionCycle}',
                    [AdmissionCycleController::class, 'destroy']
                )->name('admission-cycles.destroy');

                // Fee Items
                Route::get(
                    '/fee-items',
                    [FeeItemController::class, 'index']
                )->name('fee-items.index');

                Route::get(
                    '/fee-items/create',
                    [FeeItemController::class, 'create']
                )->name('fee-items.create');

                Route::post(
                    '/fee-items',
                    [FeeItemController::class, 'store']
                )->name('fee-items.store');

                Route::get(
                    '/fee-items/{feeItem}/edit',
                    [FeeItemController::class, 'edit']
                )->name('fee-items.edit');

                Route::put(
                    '/fee-items/{feeItem}',
                    [FeeItemController::class, 'update']
                )->name('fee-items.update');

                Route::delete(
                    '/fee-items/{feeItem}',
                    [FeeItemController::class, 'destroy']
                )->name('fee-items.destroy');

                // Assessment Plans
                Route::get('/assessment-plans', [AssessmentPlanController::class, 'index'])
                    ->name('assessment-plans.index');
                Route::get('/assessment-plans/create', [AssessmentPlanController::class, 'create'])
                    ->name('assessment-plans.create');
                Route::post('/assessment-plans', [AssessmentPlanController::class, 'store'])
                    ->name('assessment-plans.store');
                Route::get('/assessment-plans/{assessmentPlan}/edit', [AssessmentPlanController::class, 'edit'])
                    ->name('assessment-plans.edit');
                Route::put('/assessment-plans/{assessmentPlan}', [AssessmentPlanController::class, 'update'])
                    ->name('assessment-plans.update');
                Route::delete('/assessment-plans/{assessmentPlan}', [AssessmentPlanController::class, 'destroy'])
                    ->name('assessment-plans.destroy');

                // Grade Scales
                Route::get('/grade-scales', [GradeScaleController::class, 'index'])
                    ->name('grade-scales.index');
                Route::get('/grade-scales/create', [GradeScaleController::class, 'create'])
                    ->name('grade-scales.create');
                Route::post('/grade-scales', [GradeScaleController::class, 'store'])
                    ->name('grade-scales.store');
                Route::get('/grade-scales/{gradeScale}/edit', [GradeScaleController::class, 'edit'])
                    ->name('grade-scales.edit');
                Route::put('/grade-scales/{gradeScale}', [GradeScaleController::class, 'update'])
                    ->name('grade-scales.update');
                Route::delete('/grade-scales/{gradeScale}', [GradeScaleController::class, 'destroy'])
                    ->name('grade-scales.destroy');

                // Fee Structures
                Route::get(
                    '/fee-structures',
                    [FeeStructureController::class, 'index']
                )->name('fee-structures.index');

                Route::get(
                    '/fee-structures/create',
                    [FeeStructureController::class, 'create']
                )->name('fee-structures.create');

                Route::post(
                    '/fee-structures',
                    [FeeStructureController::class, 'store']
                )->name('fee-structures.store');

                Route::get(
                    '/fee-structures/{feeStructure}/edit',
                    [FeeStructureController::class, 'edit']
                )->name('fee-structures.edit');

                Route::put(
                    '/fee-structures/{feeStructure}',
                    [FeeStructureController::class, 'update']
                )->name('fee-structures.update');

                Route::delete(
                    '/fee-structures/{feeStructure}',
                    [FeeStructureController::class, 'destroy']
                )->name('fee-structures.destroy');
            });

    });
