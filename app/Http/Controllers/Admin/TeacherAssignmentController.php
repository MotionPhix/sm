<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeacherAssignmentRequest;
use App\Http\Requests\Admin\UpdateTeacherAssignmentRequest;
use App\Models\ClassStreamAssignment;
use App\Models\Role;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TeacherAssignmentController extends Controller
{
    /**
     * Display a listing of teacher assignments.
     */
    public function index(Request $request): Response
    {
        $school = $request->user()->activeSchool;

        $assignments = TeacherAssignment::query()
            ->with(['teacher', 'classroom.schoolClass', 'classroom.stream', 'subject'])
            ->get();

        return Inertia::render('admin/settings/teacher-assignments/Index', [
            'assignments' => $assignments,
        ]);
    }

    /**
     * Show the form for creating a new teacher assignment.
     */
    public function create(Request $request): Response
    {
        $school = $request->user()->activeSchool;

        return Inertia::render('admin/settings/teacher-assignments/Create', [
            'teachers' => $this->getTeachers($school),
            'classrooms' => $this->getClassrooms($school),
            'subjects' => $this->getSubjects($school),
        ]);
    }

    /**
     * Store newly created teacher assignments (one per subject).
     */
    public function store(StoreTeacherAssignmentRequest $request): RedirectResponse
    {
        $classroom = $this->resolveClassroom($request->school_class_id, $request->stream_id);

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($request, $classroom, &$created, &$skipped) {
            foreach ($request->subject_ids as $subjectId) {
                $assignment = TeacherAssignment::firstOrCreate([
                    'user_id' => $request->user_id,
                    'class_stream_assignment_id' => $classroom->id,
                    'subject_id' => $subjectId,
                ]);

                if ($assignment->wasRecentlyCreated) {
                    $created++;
                } else {
                    $skipped++;
                }
            }
        });

        $message = "{$created} teacher assignment(s) created successfully.";
        if ($skipped > 0) {
            $message .= " {$skipped} duplicate(s) skipped.";
        }

        return redirect()
            ->route('admin.settings.teacher-assignments.index')
            ->with('success', $message);
    }

    /**
     * Show the form for editing a teacher assignment.
     */
    public function edit(Request $request, TeacherAssignment $teacherAssignment): Response
    {
        $school = $request->user()->activeSchool;

        $teacherAssignment->load(['teacher', 'classroom.schoolClass', 'classroom.stream', 'subject']);

        return Inertia::render('admin/settings/teacher-assignments/Edit', [
            'assignment' => $teacherAssignment,
            'teachers' => $this->getTeachers($school),
            'classrooms' => $this->getClassrooms($school),
            'subjects' => $this->getSubjects($school),
        ]);
    }

    /**
     * Update a teacher assignment.
     */
    public function update(UpdateTeacherAssignmentRequest $request, TeacherAssignment $teacherAssignment): RedirectResponse
    {
        $classroom = $this->resolveClassroom($request->school_class_id, $request->stream_id);

        $teacherAssignment->update([
            'user_id' => $request->user_id,
            'class_stream_assignment_id' => $classroom->id,
            'subject_id' => $request->subject_id,
        ]);

        return redirect()
            ->route('admin.settings.teacher-assignments.index')
            ->with('success', 'Teacher assignment updated successfully.');
    }

    /**
     * Remove a teacher assignment.
     */
    public function destroy(TeacherAssignment $teacherAssignment): RedirectResponse
    {
        $teacherAssignment->delete();

        return redirect()
            ->route('admin.settings.teacher-assignments.index')
            ->with('success', 'Teacher assignment deleted successfully.');
    }

    /**
     * Get teachers (staff with teacher or head_teacher role in this school).
     */
    private function getTeachers($school): \Illuminate\Support\Collection
    {
        $teacherRoleIds = Role::whereIn('name', ['teacher', 'head_teacher'])
            ->pluck('id');

        return User::query()
            ->whereHas('schools', function ($q) use ($school, $teacherRoleIds) {
                $q->where('school_user.school_id', $school->id)
                    ->where('school_user.is_active', true)
                    ->whereIn('school_user.role_id', $teacherRoleIds);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    /**
     * Get classrooms for the current academic year.
     */
    private function getClassrooms($school): \Illuminate\Support\Collection
    {
        return ClassStreamAssignment::query()
            ->with(['schoolClass', 'stream'])
            ->whereHas('academicYear', function ($q) {
                $q->where('is_current', true);
            })
            ->get();
    }

    /**
     * Get subjects for this school.
     */
    private function getSubjects($school): \Illuminate\Support\Collection
    {
        return Subject::where('school_id', $school->id)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }

    /**
     * Resolve a ClassStreamAssignment from class + stream for the current academic year.
     */
    private function resolveClassroom(int $schoolClassId, int $streamId): ClassStreamAssignment
    {
        return ClassStreamAssignment::query()
            ->where('school_class_id', $schoolClassId)
            ->where('stream_id', $streamId)
            ->whereHas('academicYear', fn ($q) => $q->where('is_current', true))
            ->firstOrFail();
    }
}
