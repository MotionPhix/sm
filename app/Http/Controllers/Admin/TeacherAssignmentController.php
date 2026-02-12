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
     * Store a newly created teacher assignment.
     */
    public function store(StoreTeacherAssignmentRequest $request): RedirectResponse
    {
        TeacherAssignment::create([
            'user_id' => $request->user_id,
            'class_stream_assignment_id' => $request->class_stream_assignment_id,
            'subject_id' => $request->subject_id,
        ]);

        return redirect()
            ->route('admin.settings.teacher-assignments.index')
            ->with('success', 'Teacher assignment created successfully.');
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
        $teacherAssignment->update([
            'user_id' => $request->user_id,
            'class_stream_assignment_id' => $request->class_stream_assignment_id,
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
}
