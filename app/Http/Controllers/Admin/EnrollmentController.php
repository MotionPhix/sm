<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EnrollmentController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        $school = $request->user()->activeSchool;

        $activeYear = AcademicYear::where('school_id', $school->id)
            ->where('is_current', true)
            ->first();

        $totalStudents = Student::where('school_id', $school->id)->count();

        $activeEnrollments = StudentEnrollment::whereHas('student', function ($q) use ($school) {
            $q->where('school_id', $school->id);
        })->where('is_active', true)->count();

        $studentsByClass = SchoolClass::where('school_id', $school->id)
            ->orderBy('order')
            ->get()
            ->map(function ($class) use ($school) {
                $count = Student::where('school_id', $school->id)
                    ->whereHas('currentClassroom', function ($q) use ($class) {
                        $q->where('school_class_id', $class->id);
                    })
                    ->count();

                return [
                    'name' => $class->name,
                    'count' => $count,
                ];
            });

        $recentEnrollments = StudentEnrollment::with(['student', 'classroom.class', 'classroom.stream'])
            ->whereHas('student', function ($q) use ($school) {
                $q->where('school_id', $school->id);
            })
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'student_name' => $enrollment->student->first_name.' '.$enrollment->student->last_name,
                    'class' => $enrollment->classroom?->class?->name,
                    'stream' => $enrollment->classroom?->stream?->name,
                    'enrollment_date' => $enrollment->enrollment_date,
                    'is_active' => $enrollment->is_active,
                ];
            });

        return Inertia::render('admin/enrollment/Index', [
            'activeYear' => $activeYear ? [
                'id' => $activeYear->id,
                'name' => $activeYear->name,
            ] : null,
            'stats' => [
                'totalStudents' => $totalStudents,
                'activeEnrollments' => $activeEnrollments,
            ],
            'studentsByClass' => $studentsByClass,
            'recentEnrollments' => $recentEnrollments,
        ]);
    }
}
