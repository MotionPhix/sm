<?php

namespace Database\Factories;

use App\Models\ClassStreamAssignment;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentEnrollment>
 */
class StudentEnrollmentFactory extends Factory
{
    protected $model = StudentEnrollment::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'class_stream_assignment_id' => ClassStreamAssignment::factory(),
            'is_active' => true,
            'enrollment_date' => $this->faker->date(),
        ];
    }

    public function forStudent(Student $student): static
    {
        return $this->state(fn (array $attributes) => [
            'student_id' => $student->id,
        ]);
    }

    public function forClassroom(ClassStreamAssignment $classroom): static
    {
        return $this->state(fn (array $attributes) => [
            'class_stream_assignment_id' => $classroom->id,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
