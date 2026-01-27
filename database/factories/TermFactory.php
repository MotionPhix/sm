<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\Term;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Term>
 */
class TermFactory extends Factory
{
    protected $model = Term::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'name' => 'Term ' . fake()->numberBetween(1, 3),
            'sequence' => fake()->numberBetween(1, 3),
            'starts_on' => now()->startOfMonth(),
            'ends_on' => now()->addMonths(3)->endOfMonth(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function forAcademicYear(AcademicYear $year): static
    {
        return $this->state(fn (array $attributes) => [
            'school_id' => $year->school_id,
            'academic_year_id' => $year->id,
        ]);
    }
}
