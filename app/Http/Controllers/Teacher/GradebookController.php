<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreGradeRequest;
use App\Models\AssessmentPlan;
use App\Models\ClassStreamAssignment;
use App\Models\Grade;
use App\Models\GradeScale;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\Term;
use App\Services\GradebookService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GradebookController extends Controller
{
    public function __construct(public GradebookService $gradebookService) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $school = $user->activeSchool;
        $academicYear = $school->currentAcademicYear;

        $assignments = TeacherAssignment::where('user_id', $user->id)
            ->whereNotNull('subject_id')
            ->whereHas('classroom', fn ($q) => $q->where('school_id', $school->id))
            ->with(['classroom.class', 'classroom.stream', 'subject'])
            ->get();

        $terms = $academicYear
            ? $academicYear->terms()->orderBy('sequence')->get()
            : collect();

        return Inertia::render('teacher/gradebook/Index', [
            'assignments' => $assignments,
            'terms' => $terms,
            'academicYear' => $academicYear,
        ]);
    }

    public function show(Request $request)
    {
        $user = $request->user();
        $school = $user->activeSchool;

        $request->validate([
            'class_stream_assignment_id' => ['required', 'integer', 'exists:class_stream_assignments,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'term_id' => ['required', 'integer', 'exists:terms,id'],
        ]);

        $classroom = ClassStreamAssignment::findOrFail($request->class_stream_assignment_id);
        $subject = Subject::findOrFail($request->subject_id);
        $term = Term::findOrFail($request->term_id);

        abort_unless($classroom->school_id === $school->id, 403);
        abort_unless(
            $this->gradebookService->isTeacherAuthorizedForGrading($user, $classroom, $subject),
            403,
            'You are not assigned to teach this subject in this class.'
        );

        $students = $this->gradebookService->getStudentsForGrading($classroom);

        $assessmentPlans = AssessmentPlan::where('term_id', $term->id)
            ->where('subject_id', $subject->id)
            ->where('is_active', true)
            ->orderBy('ordering')
            ->get();

        // Build a grades matrix: [student_id][assessment_plan_id] => grade
        $grades = Grade::where('class_stream_assignment_id', $classroom->id)
            ->whereIn('assessment_plan_id', $assessmentPlans->pluck('id'))
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->groupBy('student_id')
            ->map(fn ($studentGrades) => $studentGrades->keyBy('assessment_plan_id'));

        return Inertia::render('teacher/gradebook/Show', [
            'classroom' => $classroom->load(['class', 'stream']),
            'subject' => $subject,
            'term' => $term,
            'students' => $students,
            'assessmentPlans' => $assessmentPlans,
            'grades' => $grades,
        ]);
    }

    public function store(StoreGradeRequest $request)
    {
        $user = $request->user();
        $school = $user->activeSchool;
        $validated = $request->validated();

        $assessmentPlan = AssessmentPlan::findOrFail($validated['assessment_plan_id']);
        $classroom = ClassStreamAssignment::findOrFail($validated['class_stream_assignment_id']);

        abort_unless($assessmentPlan->school_id === $school->id, 403);
        abort_unless($classroom->school_id === $school->id, 403);

        // Check if any grades are locked
        $lockedCount = Grade::where('assessment_plan_id', $assessmentPlan->id)
            ->where('class_stream_assignment_id', $classroom->id)
            ->where('is_locked', true)
            ->count();

        if ($lockedCount > 0) {
            return back()->withErrors([
                'grades' => 'Grades for this assessment have been locked and cannot be modified.',
            ]);
        }

        $subject = $assessmentPlan->subject;
        abort_unless(
            $this->gradebookService->isTeacherAuthorizedForGrading($user, $classroom, $subject),
            403,
            'You are not assigned to teach this subject in this class.'
        );

        $this->gradebookService->saveGrades(
            $assessmentPlan,
            $classroom,
            $validated['grades'],
            $user,
        );

        return back()->with('success', 'Grades saved successfully.');
    }

    public function summary(Request $request)
    {
        $user = $request->user();
        $school = $user->activeSchool;

        $request->validate([
            'class_stream_assignment_id' => ['required', 'integer', 'exists:class_stream_assignments,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'term_id' => ['required', 'integer', 'exists:terms,id'],
        ]);

        $classroom = ClassStreamAssignment::findOrFail($request->class_stream_assignment_id);
        $subject = Subject::findOrFail($request->subject_id);
        $term = Term::findOrFail($request->term_id);

        abort_unless($classroom->school_id === $school->id, 403);

        $gradeScale = GradeScale::where('school_id', $school->id)->first();

        if (! $gradeScale) {
            return back()->withErrors([
                'grade_scale' => 'No grading scale has been configured. Please ask an administrator to set one up.',
            ]);
        }

        $results = $this->gradebookService->computeClassResults($classroom, $subject, $term, $gradeScale);

        $assessmentPlans = AssessmentPlan::where('term_id', $term->id)
            ->where('subject_id', $subject->id)
            ->where('is_active', true)
            ->orderBy('ordering')
            ->get();

        // Get all grades for the detail view
        $grades = Grade::where('class_stream_assignment_id', $classroom->id)
            ->whereIn('assessment_plan_id', $assessmentPlans->pluck('id'))
            ->get()
            ->groupBy('student_id')
            ->map(fn ($studentGrades) => $studentGrades->keyBy('assessment_plan_id'));

        return Inertia::render('teacher/gradebook/Summary', [
            'classroom' => $classroom->load(['class', 'stream']),
            'subject' => $subject,
            'term' => $term,
            'results' => $results,
            'assessmentPlans' => $assessmentPlans,
            'grades' => $grades,
            'gradeScale' => $gradeScale->load('steps'),
        ]);
    }
}
