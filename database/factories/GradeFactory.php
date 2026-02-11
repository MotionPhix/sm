<?php

namespace Database\Factories;

use App\Models\AssessmentPlan;
use App\Models\ClassStreamAssignment;
use App\Models\Grade;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradeFactory extends Factory
{
    protected $model = Grade::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'student_id' => Student::factory(),
            'assessment_plan_id' => AssessmentPlan::factory(),
            'class_stream_assignment_id' => ClassStreamAssignment::factory(),
            'score' => fake()->randomFloat(2, 0, 100),
            'remarks' => fake()->optional(0.3)->sentence(),
            'entered_by' => User::factory(),
            'entered_at' => now(),
            'is_locked' => false,
        ];
    }

    public function forStudent(Student $student): static
    {
        return $this->state(fn (array $attributes) => [
            'student_id' => $student->id,
            'school_id' => $student->school_id,
        ]);
    }

    public function forAssessment(AssessmentPlan $assessmentPlan): static
    {
        return $this->state(fn (array $attributes) => [
            'assessment_plan_id' => $assessmentPlan->id,
            'school_id' => $assessmentPlan->school_id,
        ]);
    }

    public function forClassroom(ClassStreamAssignment $classroom): static
    {
        return $this->state(fn (array $attributes) => [
            'class_stream_assignment_id' => $classroom->id,
            'school_id' => $classroom->school_id,
        ]);
    }

    public function locked(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_locked' => true,
        ]);
    }
}
