<?php

namespace Database\Factories;

use App\Models\GradeScale;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradeScaleFactory extends Factory
{
    protected $model = GradeScale::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'name' => fake()->unique()->words(2, true) . ' Scale',
            'description' => fake()->optional()->sentence(),
        ];
    }

    public function forSchool(School $school): static
    {
        return $this->state(fn (array $attributes) => [
            'school_id' => $school->id,
        ]);
    }
}
