<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AssessmentPlan;
use App\Models\AttendanceRecord;
use App\Models\ClassStreamAssignment;
use App\Models\Grade;
use App\Models\GradeScale;
use App\Models\Student;
use App\Models\TeacherAssignment;
use App\Models\Term;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClassReportController extends Controller
{
    public function index(Request $request): Response
    {
        $school = $request->user()->activeSchool;
        $academicYear = $school->currentAcademicYear;

        $assignments = TeacherAssignment::where('user_id', $request->user()->id)
            ->whereHas('classroom', fn ($q) => $q->where('school_id', $school->id))
            ->with(['classroom.schoolClass', 'classroom.stream', 'subject'])
            ->get();

        // Group by classroom to show unique classes with their subjects
        $classrooms = $assignments->groupBy('class_stream_assignment_id')->map(function ($group) {
            $first = $group->first();

            return [
                'classroom_id' => $first->class_stream_assignment_id,
                'classroom' => $first->classroom,
                'subjects' => $group->map(fn ($a) => $a->subject)->filter()->values(),
            ];
        })->values();

        $terms = $academicYear
            ? $academicYear->terms()->orderBy('sequence')->get()
            : collect();

        return Inertia::render('teacher/class-reports/Index', [
            'academicYear' => $academicYear,
            'classrooms' => $classrooms,
            'terms' => $terms,
        ]);
    }

    public function show(Request $request): Response
    {
        $user = $request->user();
        $school = $user->activeSchool;

        $request->validate([
            'class_stream_assignment_id' => ['required', 'integer', 'exists:class_stream_assignments,id'],
            'term_id' => ['required', 'integer', 'exists:terms,id'],
        ]);

        $classroom = ClassStreamAssignment::findOrFail($request->class_stream_assignment_id);
        $term = Term::findOrFail($request->term_id);

        abort_unless($classroom->school_id === $school->id, 403);

        // Get teacher's subject assignments for this class
        $teacherAssignments = TeacherAssignment::where('user_id', $user->id)
            ->where('class_stream_assignment_id', $classroom->id)
            ->whereNotNull('subject_id')
            ->with('subject')
            ->get();

        abort_if($teacherAssignments->isEmpty(), 403, 'You are not assigned to this class.');

        $subjectIds = $teacherAssignments->pluck('subject_id')->filter();

        // Get enrolled students
        $students = Student::whereHas('enrollments', function ($q) use ($classroom) {
            $q->where('class_stream_assignment_id', $classroom->id)
                ->where('is_active', true);
        })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $studentIds = $students->pluck('id');

        // Get grade scale
        $gradeScale = GradeScale::where('school_id', $school->id)->first();

        // Build subject analysis
        $subjectAnalysis = $this->buildSubjectAnalysis(
            $subjectIds,
            $term,
            $classroom,
            $studentIds,
            $gradeScale,
        );

        // Build student performance
        $studentPerformance = $this->buildStudentPerformance(
            $students,
            $subjectIds,
            $term,
            $classroom,
            $gradeScale,
        );

        // Get attendance stats
        $attendanceStats = $this->buildAttendanceStats(
            $school,
            $classroom,
            $term,
            $studentIds,
        );

        // Overall class average
        $overallAverage = $studentPerformance->isNotEmpty()
            ? round($studentPerformance->avg('percentage'), 1)
            : 0;

        return Inertia::render('teacher/class-reports/Show', [
            'classroom' => $classroom->load(['schoolClass', 'stream']),
            'term' => $term,
            'totalStudents' => $students->count(),
            'overallAverage' => $overallAverage,
            'attendanceStats' => $attendanceStats,
            'subjectAnalysis' => $subjectAnalysis,
            'studentPerformance' => $studentPerformance,
            'gradeScale' => $gradeScale?->load('steps'),
        ]);
    }

    /**
     * Build per-subject analysis with average score, pass rate, and grade distribution.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function buildSubjectAnalysis(
        \Illuminate\Support\Collection $subjectIds,
        Term $term,
        ClassStreamAssignment $classroom,
        \Illuminate\Support\Collection $studentIds,
        ?GradeScale $gradeScale,
    ): \Illuminate\Support\Collection {
        return $subjectIds->map(function ($subjectId) use ($term, $classroom, $studentIds, $gradeScale) {
            $assessmentPlans = AssessmentPlan::where('term_id', $term->id)
                ->where('subject_id', $subjectId)
                ->where('is_active', true)
                ->orderBy('ordering')
                ->get();

            if ($assessmentPlans->isEmpty()) {
                $subject = \App\Models\Subject::find($subjectId);

                return [
                    'subject_id' => $subjectId,
                    'subject_name' => $subject?->name ?? 'Unknown',
                    'subject_code' => $subject?->code ?? '',
                    'average_score' => 0,
                    'pass_rate' => 0,
                    'total_graded' => 0,
                    'total_students' => $studentIds->count(),
                    'grade_distribution' => [],
                ];
            }

            $subject = $assessmentPlans->first()->subject;

            // Get all grades for this subject's assessments in this classroom
            $grades = Grade::where('class_stream_assignment_id', $classroom->id)
                ->whereIn('assessment_plan_id', $assessmentPlans->pluck('id'))
                ->whereIn('student_id', $studentIds)
                ->get()
                ->groupBy('student_id');

            // Calculate weighted percentage per student
            $studentPercentages = $studentIds->map(function ($studentId) use ($grades, $assessmentPlans) {
                $studentGrades = $grades->get($studentId, collect());

                if ($studentGrades->isEmpty()) {
                    return null;
                }

                $weightedTotal = 0;
                $totalWeight = 0;

                foreach ($assessmentPlans as $plan) {
                    $grade = $studentGrades->firstWhere('assessment_plan_id', $plan->id);

                    if ($grade && $grade->score !== null && $plan->max_score > 0) {
                        $pct = ($grade->score / $plan->max_score) * 100;
                        $weightedTotal += $pct * ($plan->weight / 100);
                        $totalWeight += (float) $plan->weight;
                    }
                }

                return $totalWeight > 0 ? ($weightedTotal / $totalWeight) * 100 : null;
            })->filter(fn ($v) => $v !== null);

            $averageScore = $studentPercentages->isNotEmpty()
                ? round($studentPercentages->avg(), 1)
                : 0;

            // Pass rate: percentage >= 50 (configurable, default 50)
            $passCount = $studentPercentages->filter(fn ($pct) => $pct >= 50)->count();
            $passRate = $studentPercentages->isNotEmpty()
                ? round(($passCount / $studentPercentages->count()) * 100, 1)
                : 0;

            // Grade distribution
            $gradeDistribution = [];
            if ($gradeScale) {
                $steps = $gradeScale->steps()->orderByDesc('min_percent')->get();
                $gradeDistribution = $steps->map(function ($step) use ($studentPercentages) {
                    $count = $studentPercentages->filter(
                        fn ($pct) => $pct >= $step->min_percent && $pct <= $step->max_percent
                    )->count();

                    return [
                        'grade' => $step->grade,
                        'comment' => $step->comment,
                        'count' => $count,
                    ];
                })->toArray();
            }

            return [
                'subject_id' => $subjectId,
                'subject_name' => $subject->name,
                'subject_code' => $subject->code ?? '',
                'average_score' => $averageScore,
                'pass_rate' => $passRate,
                'total_graded' => $studentPercentages->count(),
                'total_students' => $studentIds->count(),
                'grade_distribution' => $gradeDistribution,
            ];
        })->values();
    }

    /**
     * Build student performance table with aggregated scores.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function buildStudentPerformance(
        \Illuminate\Database\Eloquent\Collection $students,
        \Illuminate\Support\Collection $subjectIds,
        Term $term,
        ClassStreamAssignment $classroom,
        ?GradeScale $gradeScale,
    ): \Illuminate\Support\Collection {
        $assessmentPlans = AssessmentPlan::where('term_id', $term->id)
            ->whereIn('subject_id', $subjectIds)
            ->where('is_active', true)
            ->get();

        if ($assessmentPlans->isEmpty()) {
            return collect();
        }

        $allGrades = Grade::where('class_stream_assignment_id', $classroom->id)
            ->whereIn('assessment_plan_id', $assessmentPlans->pluck('id'))
            ->whereIn('student_id', $students->pluck('id'))
            ->get();

        $plansBySubject = $assessmentPlans->groupBy('subject_id');

        $results = $students->map(function (Student $student) use ($allGrades, $plansBySubject, $gradeScale, $subjectIds) {
            $studentGrades = $allGrades->where('student_id', $student->id);

            $subjectScores = $subjectIds->map(function ($subjectId) use ($studentGrades, $plansBySubject) {
                $plans = $plansBySubject->get($subjectId, collect());
                $weightedTotal = 0;
                $totalWeight = 0;

                foreach ($plans as $plan) {
                    $grade = $studentGrades->firstWhere('assessment_plan_id', $plan->id);

                    if ($grade && $grade->score !== null && $plan->max_score > 0) {
                        $pct = ($grade->score / $plan->max_score) * 100;
                        $weightedTotal += $pct * ($plan->weight / 100);
                        $totalWeight += (float) $plan->weight;
                    }
                }

                $percentage = $totalWeight > 0 ? ($weightedTotal / $totalWeight) * 100 : null;

                return [
                    'subject_id' => $subjectId,
                    'percentage' => $percentage !== null ? round($percentage, 1) : null,
                ];
            })->keyBy('subject_id')->toArray();

            $validScores = collect($subjectScores)->filter(fn ($s) => $s['percentage'] !== null);
            $overallPercentage = $validScores->isNotEmpty() ? round($validScores->avg('percentage'), 1) : 0;

            // Resolve overall grade
            $overallGrade = null;
            $overallComment = null;
            if ($gradeScale && $overallPercentage > 0) {
                $step = $gradeScale->steps()
                    ->where('min_percent', '<=', $overallPercentage)
                    ->where('max_percent', '>=', $overallPercentage)
                    ->first();
                $overallGrade = $step?->grade;
                $overallComment = $step?->comment;
            }

            return [
                'student_id' => $student->id,
                'student_name' => $student->last_name.', '.$student->first_name,
                'admission_number' => $student->admission_number,
                'gender' => $student->gender,
                'subject_scores' => $subjectScores,
                'percentage' => $overallPercentage,
                'grade' => $overallGrade,
                'comment' => $overallComment,
            ];
        });

        // Sort by percentage descending and assign ranks
        $results = $results->sortByDesc('percentage')->values();
        $rank = 0;
        $previousPercentage = null;

        return $results->map(function ($result, $index) use (&$rank, &$previousPercentage) {
            if ($result['percentage'] !== $previousPercentage) {
                $rank = $index + 1;
            }
            $previousPercentage = $result['percentage'];
            $result['rank'] = $rank;

            return $result;
        });
    }

    /**
     * Build attendance statistics for the class during the term period.
     *
     * @return array{rate: float, present: int, absent: int, late: int, excused: int, total_days: int}
     */
    private function buildAttendanceStats(
        $school,
        ClassStreamAssignment $classroom,
        Term $term,
        \Illuminate\Support\Collection $studentIds,
    ): array {
        $records = AttendanceRecord::where('school_id', $school->id)
            ->where('school_class_id', $classroom->school_class_id)
            ->when($classroom->stream_id, fn ($q) => $q->where('stream_id', $classroom->stream_id))
            ->whereIn('student_id', $studentIds)
            ->whereBetween('date', [$term->starts_on, $term->ends_on])
            ->get();

        $present = $records->where('status', 'present')->count();
        $absent = $records->where('status', 'absent')->count();
        $late = $records->where('status', 'late')->count();
        $excused = $records->where('status', 'excused')->count();
        $total = $records->count();

        $rate = $total > 0
            ? round((($present + $late) / $total) * 100, 1)
            : 0;

        $totalDays = $records->pluck('date')->unique()->count();

        return [
            'rate' => $rate,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'excused' => $excused,
            'total_records' => $total,
            'total_days' => $totalDays,
        ];
    }
}
