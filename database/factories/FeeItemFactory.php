<?php

namespace Database\Factories;

use App\Models\FeeItem;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeeItemFactory extends Factory
{
    protected $model = FeeItem::class;

    public function definition(): array
    {
        $categories = array_keys(FeeItem::categories());

        return [
            'school_id' => School::factory(),
            'name' => $this->faker->word() . ' Fee',
            'description' => $this->faker->sentence(),
            'code' => strtoupper($this->faker->unique()->bothify('???')),
            'category' => $this->faker->randomElement($categories),
            'is_mandatory' => $this->faker->boolean(80),
            'is_active' => true,
        ];
    }

    public function tuition(): self
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Tuition',
            'code' => 'TUI',
            'category' => 'tuition',
            'is_mandatory' => true,
        ]);
    }

    public function exam(): self
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Examination Fees',
            'code' => 'EXM',
            'category' => 'exam',
            'is_mandatory' => true,
        ]);
    }

    public function development(): self
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Development Levy',
            'code' => 'DEV',
            'category' => 'development',
            'is_mandatory' => true,
        ]);
    }

    public function sports(): self
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Sports Fees',
            'code' => 'SPT',
            'category' => 'extra_curriculum',
            'is_mandatory' => false,
        ]);
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function forSchool(School $school): self
    {
        return $this->state(fn (array $attributes) => [
            'school_id' => $school->id,
        ]);
    }
}
