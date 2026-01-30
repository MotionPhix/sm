<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\ClassStreamAssignment;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Stream;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClassStreamAssignment>
 */
final class ClassStreamAssignmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'school_class_id' => SchoolClass::factory(),
            'stream_id' => Stream::factory(),
            'capacity' => $this->faker->numberBetween(30, 50),
            'is_active' => true,
        ];
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

    public function forClass(SchoolClass $schoolClass): static
    {
        return $this->state(fn (array $attributes) => [
            'school_class_id' => $schoolClass->id,
        ]);
    }

    public function forStream(Stream $stream): static
    {
        return $this->state(fn (array $attributes) => [
            'stream_id' => $stream->id,
        ]);
    }

    public function withCapacity(int $capacity): static
    {
        return $this->state(fn (array $attributes) => [
            'capacity' => $capacity,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
