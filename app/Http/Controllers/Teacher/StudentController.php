<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentController extends Controller
{
    public function index(Request $request): Response
    {
        $school = $request->user()->activeSchool;
        $currentYear = AcademicYear::query()
            ->where('school_id', $school->id)
            ->where('is_current', true)
            ->first();

        // Get classroom IDs (class_stream_assignment_ids) for this teacher
        $classroomIds = TeacherAssignment::where('user_id', $request->user()->id)
            ->pluck('class_stream_assignment_id');

        $studentsQuery = Student::query()
            ->where('school_id', $school->id)
            ->whereHas('enrollments', function ($query) use ($classroomIds) {
                $query->where('is_active', true)
                    ->whereIn('class_stream_assignment_id', $classroomIds);
            });

        $totalStudents = (clone $studentsQuery)->count();

        $students = $classroomIds->isNotEmpty()
            ? $studentsQuery
                ->with(['enrollments' => function ($query) use ($classroomIds) {
                    $query->where('is_active', true)
                        ->whereIn('class_stream_assignment_id', $classroomIds)
                        ->with(['classroom.schoolClass', 'classroom.stream']);
                }])
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->paginate(30)
                ->withQueryString()
            : collect();

        return Inertia::render('teacher/students/Index', [
            'students' => $students,
            'academicYear' => $currentYear,
            'totalStudents' => $totalStudents,
        ]);
    }
}
