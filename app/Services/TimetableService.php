<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\ClassStreamAssignment;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Support\Collection;

class TimetableService
{
    /**
     * Validate if assigning a teacher to a class/stream would cause a clash.
     *
     * @param User $teacher The teacher to assign
     * @param ClassStreamAssignment $assignment The class/stream assignment
     * @param array<string, array<string, mixed>> $scheduleData Schedule data containing days and periods
     * @param int|null $exceptAssignmentId Assignment ID to exclude from checks (for updates)
     * @return array{teacher_clashes: array, class_clashes: array} Array of clashing assignments
     */
    public function validateAssignmentClash(
        User $teacher,
        ClassStreamAssignment $assignment,
        array $scheduleData,
        ?int $exceptAssignmentId = null
    ): array {
        $clashes = [
            'teacher_clashes' => [],
            'class_clashes' => [],
        ];

        // Get the academic year from the assignment for proper scoping
        $academicYearId = $assignment->academic_year_id;

        // Check teacher clashes - same teacher assigned to different classes at the same time
        $teacherAssignments = TeacherAssignment::query()
            ->where('user_id', $teacher->id)
            ->where('class_stream_assignment_id', '!=', $assignment->id)
            ->when($exceptAssignmentId, fn ($query, $id) => $query->where('id', '!=', $id))
            ->whereHas('classroom', function ($query) use ($academicYearId) {
                $query->where('academic_year_id', $academicYearId);
            })
            ->with(['classroom.class', 'classroom.stream'])
            ->get();

        foreach ($teacherAssignments as $existingAssignment) {
            $existingSchedule = $this->parseScheduleData($existingAssignment->schedule_data);

            foreach ($scheduleData as $day => $periods) {
                if (!isset($existingSchedule[$day])) {
                    continue;
                }

                $conflictingPeriods = array_intersect_key(
                    is_array($periods) ? $periods : [],
                    $existingSchedule[$day]
                );

                if (!empty($conflictingPeriods)) {
                    $clashes['teacher_clashes'][] = [
                        'assignment' => $existingAssignment,
                        'day' => $day,
                        'conflicting_periods' => array_keys($conflictingPeriods),
                        'class_name' => $existingAssignment->classroom?->class?->name,
                        'stream_name' => $existingAssignment->classroom?->stream?->name,
                    ];
                }
            }
        }

        // Check class/stream clashes - same class assigned to different teachers at the same time
        $classAssignments = TeacherAssignment::query()
            ->where('class_stream_assignment_id', $assignment->id)
            ->when($exceptAssignmentId, fn ($query, $id) => $query->where('id', '!=', $id))
            ->with(['teacher', 'classroom.class', 'classroom.stream'])
            ->get();

        foreach ($classAssignments as $existingAssignment) {
            $existingSchedule = $this->parseScheduleData($existingAssignment->schedule_data);

            foreach ($scheduleData as $day => $periods) {
                if (!isset($existingSchedule[$day])) {
                    continue;
                }

                $conflictingPeriods = array_intersect_key(
                    is_array($periods) ? $periods : [],
                    $existingSchedule[$day]
                );

                if (!empty($conflictingPeriods)) {
                    $clashes['class_clashes'][] = [
                        'assignment' => $existingAssignment,
                        'day' => $day,
                        'conflicting_periods' => array_keys($conflictingPeriods),
                        'teacher_name' => $existingAssignment->teacher?->name,
                    ];
                }
            }
        }

        return $clashes;
    }

    /**
     * Check if a teacher is available during a specific time slot.
     *
     * @param User $teacher The teacher to check
     * @param string $day Day of week (monday, tuesday, etc.)
     * @param string $period Period identifier
     * @param int|null $academicYearId Academic year ID to scope the check
     * @param int|null $exceptAssignmentId Assignment ID to exclude from checks
     * @return bool True if available, false if there's a clash
     */
    public function isTeacherAvailable(
        User $teacher,
        string $day,
        string $period,
        ?int $academicYearId = null,
        ?int $exceptAssignmentId = null
    ): bool {
        $query = TeacherAssignment::query()
            ->where('user_id', $teacher->id)
            ->when($exceptAssignmentId, fn ($q, $id) => $q->where('id', '!=', $id));

        if ($academicYearId) {
            $query->whereHas('classroom', function ($q) use ($academicYearId) {
                $q->where('academic_year_id', $academicYearId);
            });
        }

        foreach ($query->get() as $assignment) {
            $schedule = $this->parseScheduleData($assignment->schedule_data);

            if (isset($schedule[$day][$period])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a class is available during a specific time slot.
     *
     * @param ClassStreamAssignment $classAssignment The class/stream assignment
     * @param string $day Day of week (monday, tuesday, etc.)
     * @param string $period Period identifier
     * @param int|null $exceptAssignmentId Assignment ID to exclude from checks
     * @return bool True if available, false if there's a clash
     */
    public function isClassAvailable(
        ClassStreamAssignment $classAssignment,
        string $day,
        string $period,
        ?int $exceptAssignmentId = null
    ): bool {
        $clashingAssignments = TeacherAssignment::query()
            ->where('class_stream_assignment_id', $classAssignment->id)
            ->when($exceptAssignmentId, fn ($query, $id) => $query->where('id', '!=', $id))
            ->get();

        foreach ($clashingAssignments as $assignment) {
            $schedule = $this->parseScheduleData($assignment->schedule_data);

            if (isset($schedule[$day][$period])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all scheduled assignments for a teacher in a given week.
     *
     * @param User $teacher The teacher
     * @param string $weekStart Start date of the week (Y-m-d)
     * @param int|null $academicYearId Optional academic year ID to filter by
     * @return array<string, array<string, array{assignment: TeacherAssignment, details: mixed}>> Array of assignments grouped by day and period
     */
    public function getTeacherWeeklySchedule(
        User $teacher,
        string $weekStart,
        ?int $academicYearId = null
    ): array {
        $query = TeacherAssignment::query()
            ->where('user_id', $teacher->id)
            ->with(['classroom.class', 'classroom.stream']);

        if ($academicYearId) {
            $query->whereHas('classroom', function ($q) use ($academicYearId) {
                $q->where('academic_year_id', $academicYearId);
            });
        }

        $assignments = $query->get();
        $schedule = [];

        foreach ($assignments as $assignment) {
            $assignmentSchedule = $this->parseScheduleData($assignment->schedule_data);

            foreach ($assignmentSchedule as $day => $periods) {
                if (!isset($schedule[$day])) {
                    $schedule[$day] = [];
                }

                foreach ($periods as $period => $details) {
                    $schedule[$day][$period] = [
                        'assignment' => $assignment,
                        'details' => $details,
                    ];
                }
            }
        }

        return $schedule;
    }

    /**
     * Get all teachers assigned to a specific class/stream.
     *
     * @param ClassStreamAssignment $classAssignment The class/stream assignment
     * @return Collection<int, TeacherAssignment>
     */
    public function getClassTeachers(ClassStreamAssignment $classAssignment): Collection
    {
        return TeacherAssignment::query()
            ->where('class_stream_assignment_id', $classAssignment->id)
            ->with(['teacher'])
            ->get();
    }

    /**
     * Get the complete timetable for a class/stream.
     *
     * @param ClassStreamAssignment $classAssignment The class/stream assignment
     * @return array<string, array<string, array{assignment: TeacherAssignment, teacher: User|null, details: mixed}>>
     */
    public function getClassWeeklySchedule(ClassStreamAssignment $classAssignment): array
    {
        $assignments = TeacherAssignment::query()
            ->where('class_stream_assignment_id', $classAssignment->id)
            ->with(['teacher', 'classroom.class', 'classroom.stream'])
            ->get();

        $schedule = [];

        foreach ($assignments as $assignment) {
            $assignmentSchedule = $this->parseScheduleData($assignment->schedule_data);

            foreach ($assignmentSchedule as $day => $periods) {
                if (!isset($schedule[$day])) {
                    $schedule[$day] = [];
                }

                foreach ($periods as $period => $details) {
                    $schedule[$day][$period] = [
                        'assignment' => $assignment,
                        'teacher' => $assignment->teacher,
                        'details' => $details,
                    ];
                }
            }
        }

        return $schedule;
    }

    /**
     * Check if a proposed schedule has any internal conflicts.
     *
     * @param array<string, array<string, mixed>> $scheduleData The schedule to validate
     * @return array<int, array{day: string, period: string}> Array of duplicate slots found
     */
    public function validateScheduleIntegrity(array $scheduleData): array
    {
        $seenSlots = [];
        $duplicates = [];

        foreach ($scheduleData as $day => $periods) {
            if (!is_array($periods)) {
                continue;
            }

            foreach (array_keys($periods) as $period) {
                $slotKey = "{$day}:{$period}";

                if (isset($seenSlots[$slotKey])) {
                    $duplicates[] = ['day' => $day, 'period' => $period];
                }

                $seenSlots[$slotKey] = true;
            }
        }

        return $duplicates;
    }

    /**
     * Get statistics for a teacher's workload.
     *
     * @param User $teacher The teacher
     * @param int|null $academicYearId Optional academic year ID to filter by
     * @return array{total_periods: int, classes_count: int, days_active: array<string>}
     */
    public function getTeacherWorkloadStats(User $teacher, ?int $academicYearId = null): array
    {
        $query = TeacherAssignment::query()
            ->where('user_id', $teacher->id);

        if ($academicYearId) {
            $query->whereHas('classroom', function ($q) use ($academicYearId) {
                $q->where('academic_year_id', $academicYearId);
            });
        }

        $assignments = $query->get();

        $totalPeriods = 0;
        $daysActive = [];

        foreach ($assignments as $assignment) {
            $schedule = $this->parseScheduleData($assignment->schedule_data);

            foreach ($schedule as $day => $periods) {
                $totalPeriods += count($periods);

                if (!in_array($day, $daysActive)) {
                    $daysActive[] = $day;
                }
            }
        }

        return [
            'total_periods' => $totalPeriods,
            'classes_count' => $assignments->count(),
            'days_active' => $daysActive,
        ];
    }

    /**
     * Parse schedule data from JSON or array format.
     *
     * @param mixed $scheduleData The schedule data to parse
     * @return array<string, array<string, mixed>>
     */
    private function parseScheduleData(mixed $scheduleData): array
    {
        if (is_string($scheduleData)) {
            $decoded = json_decode($scheduleData, true);

            return is_array($decoded) ? $decoded : [];
        }

        return is_array($scheduleData) ? $scheduleData : [];
    }
}
