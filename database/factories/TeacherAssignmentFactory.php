<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\ClassStreamAssignment;
use App\Models\School;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeacherAssignment>
 */
class TeacherAssignmentFactory extends Factory
{
    protected $model = TeacherAssignment::class;

    public function definition(): array
    {
        $school = School::factory()->create();
        $year = AcademicYear::factory()->forSchool($school)->current()->create();

        return [
            'user_id' => User::factory(),
            'class_stream_assignment_id' => ClassStreamAssignment::factory()->forAcademicYear($year),
            'school_id' => $school->id,
            'academic_year_id' => $year->id,
            'schedule_data' => null,
        ];
    }

    public function forTeacher(User $teacher): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $teacher->id,
        ]);
    }

    public function forClassroom(ClassStreamAssignment $classroom): static
    {
        return $this->state(fn (array $attributes) => [
            'class_stream_assignment_id' => $classroom->id,
            'school_id' => $classroom->school_id,
            'academic_year_id' => $classroom->academic_year_id,
        ]);
    }

    public function withSchedule(array $schedule): static
    {
        return $this->state(fn (array $attributes) => [
            'schedule_data' => $schedule,
        ]);
    }

    /**
     * Create a schedule for Monday morning periods.
     */
    public function mondayMorning(): static
    {
        return $this->withSchedule([
            'monday' => [
                'period_1' => ['subject' => 'Math'],
                'period_2' => ['subject' => 'Math'],
            ],
        ]);
    }

    /**
     * Create a full week schedule.
     */
    public function fullWeek(): static
    {
        $schedule = [];
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

        foreach ($days as $day) {
            $schedule[$day] = [
                'period_1' => ['subject' => 'Subject A'],
                'period_4' => ['subject' => 'Subject B'],
            ];
        }

        return $this->withSchedule($schedule);
    }
}
