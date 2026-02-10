<?php

namespace Database\Factories;

use App\Models\AdmissionCycle;
use App\Models\Applicant;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Applicant>
 */
class ApplicantFactory extends Factory
{
    protected $model = Applicant::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'admission_cycle_id' => AdmissionCycle::factory(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'national_id' => $this->faker->optional(0.3)->numerify('############'),
            'date_of_birth' => $this->faker->dateTimeBetween('-18 years', '-6 years'),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'status' => 'applied',
        ];
    }

    public function admitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'admitted',
        ]);
    }

    public function forSchool(School $school): static
    {
        return $this->state(fn (array $attributes) => [
            'school_id' => $school->id,
            'admission_cycle_id' => AdmissionCycle::factory()->forSchool($school),
        ]);
    }
}
