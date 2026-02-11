<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\AssessmentPlan;
use App\Models\School;
use App\Models\Subject;
use App\Models\Term;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssessmentPlanFactory extends Factory
{
    protected $model = AssessmentPlan::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'term_id' => Term::factory(),
            'subject_id' => Subject::factory(),
            'name' => fake()->randomElement(['Midterm Test', 'Coursework', 'Final Exam', 'Quiz', 'Assignment']),
            'ordering' => fake()->numberBetween(1, 5),
            'max_score' => 100,
            'weight' => fake()->randomElement([20, 25, 30, 50]),
            'is_active' => true,
        ];
    }

    public function forSchool(School $school): static
    {
        return $this->state(fn (array $attributes) => [
            'school_id' => $school->id,
        ]);
    }

    public function forTerm(Term $term): static
    {
        return $this->state(fn (array $attributes) => [
            'term_id' => $term->id,
            'academic_year_id' => $term->academic_year_id,
            'school_id' => $term->school_id,
        ]);
    }

    public function forSubject(Subject $subject): static
    {
        return $this->state(fn (array $attributes) => [
            'subject_id' => $subject->id,
            'school_id' => $subject->school_id,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
