<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\AdmissionCycle;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdmissionCycle>
 */
class AdmissionCycleFactory extends Factory
{
    protected $model = AdmissionCycle::class;

    public function definition(): array
    {
        $start = now()->subMonths(2);
        $end = now()->addMonths(2);

        return [
            'school_id' => School::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'name' => $start->format('Y').' Intake',
            'is_active' => false,
            'starts_at' => $start,
            'ends_at' => $end,
            'target_class' => $this->faker->randomElement(['Form 1', 'Form 2', 'Form 3']),
            'max_intake' => $this->faker->numberBetween(30, 120),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function forSchool(School $school): static
    {
        return $this->state(fn (array $attributes) => [
            'school_id' => $school->id,
        ]);
    }

    public function forAcademicYear(AcademicYear $academicYear): static
    {
        return $this->state(fn (array $attributes) => [
            'academic_year_id' => $academicYear->id,
        ]);
    }
}
