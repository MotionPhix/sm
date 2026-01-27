<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\ClassStreamAssignment;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Stream;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassStreamAssignment>
 */
class ClassStreamAssignmentFactory extends Factory
{
    protected $model = ClassStreamAssignment::class;

    public function definition(): array
    {
        $school = School::factory()->create();

        return [
            'school_id' => $school->id,
            'academic_year_id' => AcademicYear::factory()->forSchool($school),
            'school_class_id' => SchoolClass::factory()->forSchool($school),
            'stream_id' => Stream::factory()->forSchool($school),
        ];
    }

    public function forSchool(School $school): static
    {
        return $this->state(fn (array $attributes) => [
            'school_id' => $school->id,
        ]);
    }

    public function forAcademicYear(AcademicYear $year): static
    {
        return $this->state(fn (array $attributes) => [
            'school_id' => $year->school_id,
            'academic_year_id' => $year->id,
        ]);
    }

    public function forClass(SchoolClass $class): static
    {
        return $this->state(fn (array $attributes) => [
            'school_id' => $class->school_id,
            'school_class_id' => $class->id,
        ]);
    }

    public function forStream(Stream $stream): static
    {
        return $this->state(fn (array $attributes) => [
            'stream_id' => $stream->id,
        ]);
    }

    public function withoutStream(): static
    {
        return $this->state(fn (array $attributes) => [
            'stream_id' => null,
        ]);
    }
}
