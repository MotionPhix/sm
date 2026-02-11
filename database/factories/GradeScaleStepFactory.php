<?php

namespace Database\Factories;

use App\Models\GradeScale;
use App\Models\GradeScaleStep;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradeScaleStepFactory extends Factory
{
    protected $model = GradeScaleStep::class;

    public function definition(): array
    {
        return [
            'grade_scale_id' => GradeScale::factory(),
            'min_percent' => 0,
            'max_percent' => 100,
            'grade' => fake()->randomElement(['A', 'B', 'C', 'D', 'E', 'F']),
            'comment' => fake()->optional()->word(),
            'ordering' => 1,
        ];
    }

    public function forGradeScale(GradeScale $gradeScale): static
    {
        return $this->state(fn (array $attributes) => [
            'grade_scale_id' => $gradeScale->id,
        ]);
    }
}
