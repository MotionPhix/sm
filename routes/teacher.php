<?php

use App\Http\Controllers\Teacher\AnnouncementController;
use App\Http\Controllers\Teacher\AttendanceController;
use App\Http\Controllers\Teacher\ClassReportController;
use App\Http\Controllers\Teacher\ExamMarkingController;
use App\Http\Controllers\Teacher\GradebookController;
use App\Http\Controllers\Teacher\StudentController;
use App\Http\Controllers\Teacher\TimetableController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified', 'school.context', 'role:teacher,head_teacher', 'academic'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {

        Route::get('/dashboard', function () {
            return Inertia::render('teacher/Dashboard');
        })->name('dashboard');

        // Attendance routes
        Route::middleware('permission:attendance.view')->group(function () {
            Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
            Route::get('/attendance/roster', [AttendanceController::class, 'roster'])->name('attendance.roster');
            Route::get('/attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');
        });
        Route::middleware('permission:attendance.record')->group(function () {
            Route::get('/attendance/record', [AttendanceController::class, 'record'])->name('attendance.record');
            Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        });
        Route::middleware('permission:attendance.export')->group(function () {
            Route::get('/attendance/export', [AttendanceController::class, 'export'])->name('attendance.export');
        });

        // Timetable routes
        Route::middleware('permission:timetable.view')->group(function () {
            Route::get('/timetable', [TimetableController::class, 'index'])->name('timetable.index');
            Route::get('/timetable/my', [TimetableController::class, 'showMyTimetable'])->name('timetable.my');
        });

        Route::middleware('permission:timetable.manage')->group(function () {
            Route::post('/timetable/check-conflicts', [TimetableController::class, 'getConflicts'])->name('timetable.check-conflicts');
        });

        // Gradebook routes
        Route::middleware('permission:exams.view')->group(function () {
            Route::get('/gradebook', [GradebookController::class, 'index'])->name('gradebook.index');
            Route::get('/gradebook/show', [GradebookController::class, 'show'])->name('gradebook.show');
            Route::get('/gradebook/summary', [GradebookController::class, 'summary'])->name('gradebook.summary');
        });
        Route::middleware('permission:exams.enter-marks')->group(function () {
            Route::post('/gradebook', [GradebookController::class, 'store'])->name('gradebook.store');
        });

        // My Students
        Route::middleware('permission:students.view')->group(function () {
            Route::get('/students', [StudentController::class, 'index'])->name('students.index');
        });

        // Exam Marking
        Route::middleware('permission:exams.enter-marks')->group(function () {
            Route::get('/exam-marking', [ExamMarkingController::class, 'index'])->name('exam-marking.index');
        });

        // Class Reports
        Route::middleware('permission:students.view')->group(function () {
            Route::get('/class-reports', [ClassReportController::class, 'index'])->name('class-reports.index');
            Route::get('/class-reports/show', [ClassReportController::class, 'show'])->name('class-reports.show');
        });

        // Announcements
        Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');

    });
