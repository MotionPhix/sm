<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\Stream;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stream>
 */
class StreamFactory extends Factory
{
    protected $model = Stream::class;

    public function definition(): array
    {
        $streams = ['A', 'B', 'C', 'D', 'E', 'North', 'South', 'East', 'West'];

        return [
            'school_id' => School::factory(),
            'name' => fake()->randomElement($streams),
        ];
    }

    public function forSchool(School $school): static
    {
        return $this->state(fn (array $attributes) => [
            'school_id' => $school->id,
        ]);
    }
}
