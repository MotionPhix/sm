<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClassReportController extends Controller
{
    public function index(Request $request): Response
    {
        $school = $request->user()->activeSchool;
        $currentYear = AcademicYear::query()
            ->where('school_id', $school->id)
            ->where('is_current', true)
            ->first();

        $assignments = TeacherAssignment::where('user_id', $request->user()->id)
            ->with(['classroom.schoolClass', 'classroom.stream'])
            ->get();

        return Inertia::render('teacher/class-reports/Index', [
            'academicYear' => $currentYear,
            'assignments' => $assignments,
        ]);
    }
}
