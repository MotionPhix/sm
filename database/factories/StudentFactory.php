<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'admission_number' => strtoupper(fake()->unique()->bothify('STD-####-??')),
            'first_name' => fake()->firstName(),
            'middle_name' => fake()->optional(0.5)->firstName(),
            'last_name' => fake()->lastName(),
            'gender' => fake()->randomElement(['male', 'female']),
            'date_of_birth' => fake()->dateTimeBetween('-18 years', '-6 years'),
            'admission_date' => fake()->dateTimeBetween('-3 years', 'now'),
            'national_id' => fake()->optional(0.3)->numerify('############'),
        ];
    }

    public function forSchool(School $school): static
    {
        return $this->state(fn (array $attributes) => [
            'school_id' => $school->id,
        ]);
    }

    public function male(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender' => 'male',
            'first_name' => fake()->firstNameMale(),
        ]);
    }

    public function female(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender' => 'female',
            'first_name' => fake()->firstNameFemale(),
        ]);
    }
}
