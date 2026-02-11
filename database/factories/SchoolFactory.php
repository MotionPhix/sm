<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<School>
 */
class SchoolFactory extends Factory
{
    protected $model = School::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid()->toString(),
            'name' => fake()->company().' School',
            'code' => strtoupper(fake()->unique()->lexify('??????')),
            'type' => fake()->randomElement(\App\Enums\SchoolType::cases())->value,
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'district' => fake()->city(),
            'country' => 'Malawi',
            'is_active' => true,
            'activated_at' => now(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'activated_at' => null,
        ]);
    }

    public function withOwner(User $owner): static
    {
        return $this->state(fn (array $attributes) => [
            'owner_id' => $owner->id,
        ]);
    }
}
