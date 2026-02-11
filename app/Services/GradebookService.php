<?php

namespace App\Services;

use App\Models\AssessmentPlan;
use App\Models\ClassStreamAssignment;
use App\Models\Grade;
use App\Models\GradeScale;
use App\Models\GradeScaleStep;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\Term;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class GradebookService
{
    /**
     * Get enrolled students for a specific classroom.
     *
     * @return Collection<int, Student>
     */
    public function getStudentsForGrading(ClassStreamAssignment $classroom): Collection
    {
        return Student::whereHas('enrollments', function ($query) use ($classroom) {
            $query->where('class_stream_assignment_id', $classroom->id)
                ->where('is_active', true);
        })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    /**
     * Save or update grades for a batch of students for one assessment.
     *
     * @param  array<int, array{student_id: int, score: float|null, remarks: string|null}>  $grades
     */
    public function saveGrades(
        AssessmentPlan $assessmentPlan,
        ClassStreamAssignment $classroom,
        array $grades,
        User $enteredBy,
    ): void {
        DB::transaction(function () use ($assessmentPlan, $classroom, $grades, $enteredBy) {
            foreach ($grades as $gradeData) {
                Grade::updateOrCreate(
                    [
                        'student_id' => $gradeData['student_id'],
                        'assessment_plan_id' => $assessmentPlan->id,
                    ],
                    [
                        'school_id' => $assessmentPlan->school_id,
                        'class_stream_assignment_id' => $classroom->id,
                        'score' => $gradeData['score'],
                        'remarks' => $gradeData['remarks'] ?? null,
                        'entered_by' => $enteredBy->id,
                        'entered_at' => now(),
                    ]
                );
            }
        });
    }

    /**
     * Compute weighted total for a student in a subject for a term.
     *
     * @return array{total_score: float, percentage: float, grade: string|null, comment: string|null}
     */
    public function computeStudentTermTotal(
        Student $student,
        Subject $subject,
        Term $term,
        GradeScale $gradeScale,
    ): array {
        $assessmentPlans = AssessmentPlan::where('term_id', $term->id)
            ->where('subject_id', $subject->id)
            ->where('is_active', true)
            ->orderBy('ordering')
            ->get();

        if ($assessmentPlans->isEmpty()) {
            return ['total_score' => 0, 'percentage' => 0, 'grade' => null, 'comment' => null];
        }

        $weightedTotal = 0;
        $totalWeight = 0;

        foreach ($assessmentPlans as $plan) {
            $grade = Grade::where('student_id', $student->id)
                ->where('assessment_plan_id', $plan->id)
                ->first();

            if ($grade && $grade->score !== null && $plan->max_score > 0) {
                $percentage = ($grade->score / $plan->max_score) * 100;
                $weightedTotal += $percentage * ($plan->weight / 100);
                $totalWeight += (float) $plan->weight;
            }
        }

        // Normalize if total weight doesn't equal 100
        $percentage = $totalWeight > 0 ? ($weightedTotal / $totalWeight) * 100 : 0;

        $step = $this->resolveGrade($percentage, $gradeScale);

        return [
            'total_score' => round($weightedTotal, 2),
            'percentage' => round($percentage, 2),
            'grade' => $step?->grade,
            'comment' => $step?->comment,
        ];
    }

    /**
     * Compute results for all students in a class for a subject in a term.
     *
     * @return Collection<int, array{student: Student, total_score: float, percentage: float, grade: string|null, comment: string|null, rank: int}>
     */
    public function computeClassResults(
        ClassStreamAssignment $classroom,
        Subject $subject,
        Term $term,
        GradeScale $gradeScale,
    ): \Illuminate\Support\Collection {
        $students = $this->getStudentsForGrading($classroom);

        $results = $students->map(function (Student $student) use ($subject, $term, $gradeScale) {
            $totals = $this->computeStudentTermTotal($student, $subject, $term, $gradeScale);

            return array_merge($totals, ['student' => $student]);
        });

        // Sort by percentage descending for ranking
        $results = $results->sortByDesc('percentage')->values();

        // Assign ranks (handle ties)
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
     * Map a percentage to a grade label using the grading scale.
     */
    public function resolveGrade(float $percentage, GradeScale $gradeScale): ?GradeScaleStep
    {
        return $gradeScale->steps()
            ->where('min_percent', '<=', $percentage)
            ->where('max_percent', '>=', $percentage)
            ->first();
    }

    /**
     * Check if a teacher is authorized to grade a particular class/subject.
     */
    public function isTeacherAuthorizedForGrading(
        User $teacher,
        ClassStreamAssignment $classroom,
        Subject $subject,
    ): bool {
        return TeacherAssignment::where('user_id', $teacher->id)
            ->where('class_stream_assignment_id', $classroom->id)
            ->where('subject_id', $subject->id)
            ->exists();
    }

    /**
     * Lock grades for an assessment in a classroom.
     */
    public function lockGrades(AssessmentPlan $assessmentPlan, ClassStreamAssignment $classroom): void
    {
        Grade::where('assessment_plan_id', $assessmentPlan->id)
            ->where('class_stream_assignment_id', $classroom->id)
            ->update(['is_locked' => true]);
    }
}
