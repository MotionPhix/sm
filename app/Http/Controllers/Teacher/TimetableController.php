<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherAssignment;
use App\Models\ClassStreamAssignment;
use App\Models\SchoolClass;
use App\Models\Stream;
use App\Models\Subject;
use App\Services\TimetableService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class TimetableController extends Controller
{
    protected $timetableService;

    public function __construct(TimetableService $timetableService)
    {
        $this->timetableService = $timetableService;
    }

    public function index(Request $request)
    {
        $school = $request->user()->activeSchool();
        
        // Get all classes for the current school
        $classes = SchoolClass::where('school_id', $school->id)
            ->with(['assignments.stream', 'assignedTeachers.teacher'])
            ->get();

        // Get teacher's assigned classes/streams for current term/day
        $teacherAssignments = TeacherAssignment::where('user_id', $request->user()->id)
            ->where('school_id', $school->id)
            ->with(['classroom.class', 'classroom.stream'])
            ->get();

        return Inertia::render('teacher/timetable/Index', [
            'classes' => $classes,
            'teacherAssignments' => $teacherAssignments,
        ]);
    }

    public function showMyTimetable(Request $request)
    {
        $school = $request->user()->activeSchool();
        
        // Get teacher's assignments and build their weekly schedule
        $teacherAssignments = TeacherAssignment::where('user_id', $request->user()->id)
            ->where('school_id', $school->id)
            ->with(['classroom.class', 'classroom.stream'])
            ->get();

        $weeklySchedule = $this->timetableService->getTeacherWeeklySchedule(
            $request->user(),
            now()->startOfWeek()->toDateString()
        );

        return response()->json([
            'timetable' => $weeklySchedule,
            'assignments' => $teacherAssignments,
        ]);
    }

    public function getConflicts(Request $request)
    {
        $data = $request->validate([
            'teacher_id' => ['required', 'exists:users,id'],
            'class_stream_assignment_id' => ['required', 'exists:class_stream_assignments,id'],
            'schedule_data' => ['required', 'array'],
        ]);

        $teacher = \App\Models\User::findOrFail($data['teacher_id']);
        $assignment = ClassStreamAssignment::findOrFail($data['class_stream_assignment_id']);

        $clashes = $this->timetableService->validateAssignmentClash(
            $teacher,
            $assignment,
            $data['schedule_data']
        );

        return response()->json($clashes);
    }
}