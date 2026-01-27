<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\AttendanceRecord;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Stream;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceRecord>
 */
class AttendanceRecordFactory extends Factory
{
    protected $model = AttendanceRecord::class;

    public function definition(): array
    {
        $school = School::factory()->create();

        return [
            'school_id' => $school->id,
            'academic_year_id' => AcademicYear::factory()->forSchool($school)->current(),
            'date' => fake()->dateTimeBetween('-30 days', 'now'),
            'school_class_id' => SchoolClass::factory()->forSchool($school),
            'stream_id' => Stream::factory()->forSchool($school),
            'student_id' => Student::factory()->forSchool($school),
            'status' => fake()->randomElement(['present', 'absent', 'late', 'excused']),
            'remarks' => fake()->optional(0.3)->sentence(),
            'recorded_by' => User::factory(),
            'recorded_at' => now(),
        ];
    }

    public function present(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'present',
        ]);
    }

    public function absent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'absent',
        ]);
    }

    public function late(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'late',
        ]);
    }

    public function excused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'excused',
        ]);
    }

    public function forStudent(Student $student): static
    {
        return $this->state(fn (array $attributes) => [
            'school_id' => $student->school_id,
            'student_id' => $student->id,
        ]);
    }

    public function forDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $date,
        ]);
    }

    public function recordedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'recorded_by' => $user->id,
        ]);
    }
}
