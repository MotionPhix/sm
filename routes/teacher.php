<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Teacher\AttendanceController;
use App\Http\Controllers\Teacher\TimetableController;

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

    });